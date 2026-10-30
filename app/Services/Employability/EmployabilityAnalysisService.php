<?php

namespace App\Services\Employability;

use App\Exceptions\CvExtractionException;
use App\Exceptions\EmployabilityAnalysisException;
use App\Models\Estudiante;
use App\Models\Vacante;
use Illuminate\Support\Facades\Storage;

class EmployabilityAnalysisService
{
    private const CV_SECTIONS = [
        'education',
        'skills',
        'experience',
        'projects',
        'certifications',
        'technologies',
        'languages',
    ];

    private const MAX_PROFILE_CHARACTERS = 8_000;

    public function __construct(
        private readonly CvTextExtractor $extractor,
        private readonly CvPrivacyRedactor $redactor,
        private readonly VacancyCriteriaBuilder $criteriaBuilder,
        private readonly GeminiEmployabilityService $gemini,
    ) {}

    public function analyze(Estudiante $estudiante, Vacante $vacante, ?string $cvContents = null): array
    {
        $cvContents ??= $this->getCvContents($estudiante);
        $cvText = '';
        $cvError = null;

        if ($cvContents !== null) {
            try {
                $cvText = $this->redactor->redact($this->extractor->extract($cvContents), $estudiante);
            } catch (CvExtractionException $exception) {
                $cvError = $exception;
                $cvContents = null;
            }
        }

        $profileText = $this->profileEvidenceText($estudiante);
        $usesCv = $cvText !== '';

        if (! $usesCv && (! $estudiante->perfilSuficienteParaAnalisis() || $profileText === '')) {
            throw new EmployabilityAnalysisException(
                'No hay un CV legible ni suficiente información profesional para analizar. Completa tu perfil (agrega una experiencia o una formación y al menos tres habilidades) o sube un CV PDF legible.'
            );
        }

        if (! $usesCv && $cvError !== null) {
            $cvContents = null;
        }

        $criteria = $this->criteriaBuilder->build($vacante);
        $raw = $this->gemini->analyze($cvText, $profileText, $vacante, $criteria);

        $cvInformation = $this->validateCvInformation($raw['cv_information'] ?? null, $cvText, $profileText);
        $criteriaResults = $this->validateCriteria($raw['criteria'] ?? null, $criteria, $cvText, $profileText);
        $recommendations = $this->validateRecommendations(
            $raw['recommendations'] ?? null,
            $criteriaResults,
            $cvText,
            $profileText,
        );
        $suggestedProfile = $this->validateParagraph($raw['suggested_profile'] ?? null, $cvText, $profileText);
        $adaptedCv = $this->validateAdaptedCv($raw['adapted_cv'] ?? null, $cvText, $profileText);
        $adaptedCv = $this->completeAdaptedCvFallback($adaptedCv, $cvInformation, $profileText);

        $weightedMatches = array_sum(array_map(
            fn (array $item): float => match ($item['match']) {
                'direct' => 1,
                'related' => 0.5,
                default => 0,
            },
            $criteriaResults,
        ));
        $score = count($criteriaResults) > 0
            ? round(($weightedMatches / count($criteriaResults)) * 100, 1)
            : null;

        return [
            'vacancy' => [
                'title' => $vacante->titulo,
                'company' => $vacante->empresa->nombre_empresa,
                'employment_type' => $vacante->tipo_empleo,
                'modality' => $vacante->modalidad,
                'careers' => $vacante->carreras->pluck('nombre')->values()->all(),
            ],
            'cv_information' => $cvInformation,
            'criteria' => $criteriaResults,
            'not_identified' => array_values(array_filter(
                $criteriaResults,
                fn (array $item): bool => $item['match'] === 'none',
            )),
            'compatibility_percentage' => $score,
            'suggested_profile' => $suggestedProfile,
            'recommendations' => $recommendations,
            'adapted_cv' => $adaptedCv,
            'cv_fingerprint' => hash('sha256', $usesCv ? (string) $cvContents : $profileText),
            'profile_only' => ! $usesCv,
            'profile_completion_percentage' => $estudiante->porcentajeCompletitud(),
            'profile_sections_to_complete' => $this->profileSectionsToComplete($estudiante, $criteria),
        ];
    }

    public function profileEvidenceText(Estudiante $estudiante): string
    {
        $profileText = $this->redactor->redactProfile($estudiante->perfilComoTexto(), $estudiante);
        $profileText = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/iu', '', $profileText) ?? $profileText;
        $profileText = preg_replace('/(?<!\w)\+?\d[\d\s().-]{7,}\d(?!\w)/u', '', $profileText) ?? $profileText;
        $profileText = preg_replace('~(?:https?://|ftp://|www\.)\S+~iu', '', $profileText) ?? $profileText;
        $profileText = preg_replace('~\b(?:[a-z0-9-]+\.)+(?:com|org|net|edu|gov|io|co|app|dev|do|info|me|biz)(?:/\S*)?\b~iu', '', $profileText) ?? $profileText;

        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\R/u', $profileText) ?: []),
            fn (string $line): bool => $line !== '',
        ));
        $priorities = ['Experiencia', 'Habilidad', 'Formación', 'Certificación', 'Idioma', 'Titular', 'Resumen'];
        $selected = [];
        $length = 0;

        foreach ($priorities as $priority) {
            foreach ($lines as $line) {
                if (! str_starts_with($line, $priority.':')) {
                    continue;
                }

                $lineLength = mb_strlen($line) + ($selected === [] ? 0 : 1);
                if ($length + $lineLength <= self::MAX_PROFILE_CHARACTERS) {
                    $selected[] = $line;
                    $length += $lineLength;
                }
            }
        }

        return implode("\n", $selected);
    }

    public function validateAdaptationSubmission(
        array $draft,
        string $cvText,
        string $profileText = '',
        bool $studentConfirmed = false,
    ): array {
        if (! $studentConfirmed) {
            throw new EmployabilityAnalysisException('Confirma que el contenido del CV adaptado refleja tus antecedentes reales.');
        }

        $profile = $draft['profile'] ?? [];
        if (! is_array($profile)
            || ! is_string($profile['text'] ?? null)
            || mb_strlen($profile['text']) > 2000
            || ! is_array($profile['evidence'] ?? [])) {
            throw new EmployabilityAnalysisException('El perfil profesional del borrador no tiene un formato válido.');
        }

        $validatedProfile = $this->validateParagraph([
            'text' => $profile['text'],
            'evidence' => $profile['evidence'],
        ], $cvText, $profileText);
        $profileVerified = $validatedProfile['text'] !== '';
        $adapted = [
            'profile' => [
                'text' => trim($profile['text']),
                'evidence' => $profileVerified ? $validatedProfile['evidence'] : [],
                'verified' => $profileVerified,
                'student_confirmed' => ! $profileVerified && trim($profile['text']) !== '',
            ],
        ];

        foreach (self::CV_SECTIONS as $section) {
            $items = $draft[$section] ?? [];
            if (! is_array($items) || count($items) > 50) {
                throw new EmployabilityAnalysisException('Una sección del CV adaptado no tiene un formato válido.');
            }

            $adapted[$section] = [];
            foreach ($items as $itemKey => $item) {
                if ($itemKey === 'manual') {
                    continue;
                }

                if (! is_array($item)
                    || ! is_string($item['text'] ?? null)
                    || mb_strlen($item['text']) > 1000) {
                    throw new EmployabilityAnalysisException('Un elemento del CV adaptado no tiene un formato válido.');
                }

                $text = trim($item['text']);
                if ($text === '') {
                    continue;
                }

                $evidence = is_string($item['evidence'] ?? null) ? trim($item['evidence']) : '';
                $source = is_string($item['source'] ?? null) ? $item['source'] : '';
                $verified = $this->hasValidEvidence($evidence, $source, $cvText, $profileText)
                    && $this->claimIsSupportedByEvidence($text, $evidence);
                $adapted[$section][] = [
                    'text' => $text,
                    'evidence' => $verified ? $evidence : '',
                    'source' => $verified ? $source : null,
                    'evidence_valid' => $verified,
                    'student_confirmed' => ! $verified,
                ];
            }

            $manualText = $draft['manual'][$section] ?? '';
            if (! is_string($manualText) || mb_strlen($manualText) > 2000) {
                throw new EmployabilityAnalysisException('El texto agregado a una sección supera el tamaño permitido.');
            }
            if (trim($manualText) !== '') {
                $adapted[$section][] = [
                    'text' => trim($manualText),
                    'evidence' => '',
                    'source' => null,
                    'evidence_valid' => false,
                    'student_confirmed' => true,
                ];
            }
        }

        $hasContent = $adapted['profile']['text'] !== '';
        foreach (self::CV_SECTIONS as $section) {
            $hasContent = $hasContent || $adapted[$section] !== [];
        }
        if (! $hasContent) {
            throw new EmployabilityAnalysisException('Agrega al menos un dato verdadero a tu CV adaptado antes de descargarlo.');
        }

        return $adapted;
    }

    private function completeAdaptedCvFallback(array $adapted, array $cvInformation, string $profileText): array
    {
        $profileSectionMap = [
            'education' => 'Formación',
            'skills' => 'Habilidad',
            'experience' => 'Experiencia',
            'certifications' => 'Certificación',
            'languages' => 'Idioma',
        ];

        foreach (self::CV_SECTIONS as $section) {
            $verifiedItems = array_values(array_filter(
                $adapted[$section],
                fn (array $item): bool => $item['evidence_valid'],
            ));

            if ($verifiedItems === []) {
                $verifiedItems = array_values(array_filter(
                    $cvInformation[$section],
                    fn (array $item): bool => $item['evidence_valid'],
                ));
            }

            if ($verifiedItems === []) {
                $label = $profileSectionMap[$section] ?? null;
                if ($label !== null) {
                    foreach (preg_split('/\R/u', $profileText) ?: [] as $line) {
                        if (! str_starts_with($line, $label.':')) {
                            continue;
                        }

                        $value = trim(mb_substr($line, mb_strlen($label) + 1));
                        if ($value !== '') {
                            $verifiedItems[] = [
                                'text' => $value,
                                'evidence' => trim($line),
                                'source' => 'perfil',
                                'evidence_valid' => true,
                            ];
                        }
                    }
                }
            }

            $adapted[$section] = $verifiedItems;
        }

        if ($adapted['profile']['text'] === '') {
            foreach (self::CV_SECTIONS as $section) {
                if ($adapted[$section] === []) {
                    continue;
                }

                $item = $adapted[$section][0];
                $adapted['profile'] = [
                    'text' => $item['text'],
                    'evidence' => [[
                        'source' => $item['source'],
                        'quote' => $item['evidence'],
                    ]],
                ];
                break;
            }
        }

        return $adapted;
    }

    private function getCvContents(Estudiante $estudiante): ?string
    {
        if (! $estudiante->cv_path) {
            return null;
        }

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($estudiante->cv_path)) {
                return Storage::disk($disk)->get($estudiante->cv_path);
            }
        }

        return null;
    }

    private function profileSectionsToComplete(Estudiante $estudiante, array $criteria): array
    {
        $criteriaText = implode(' ', array_column($criteria, 'text'));
        $sections = [
            ['label' => 'Experiencia profesional', 'empty' => ! $estudiante->experiencias()->exists(), 'pattern' => '/experiencia|pasant[ií]a|empresa|cargo|trabajo/iu'],
            ['label' => 'Habilidades', 'empty' => $estudiante->habilidades()->count() < 3, 'pattern' => '/habilidad|competencia|conocimiento|destreza/iu'],
            ['label' => 'Formación académica', 'empty' => ! $estudiante->formaciones()->exists(), 'pattern' => '/formaci[oó]n|educaci[oó]n|t[ií]tulo|grado|estudio/iu'],
            ['label' => 'Idiomas', 'empty' => ! $estudiante->idiomas()->exists(), 'pattern' => '/idioma|ingl[eé]s|biling[uü]e/iu'],
            ['label' => 'Certificaciones', 'empty' => ! $estudiante->certificaciones()->exists(), 'pattern' => '/certificaci[oó]n|licencia|acreditaci[oó]n/iu'],
        ];

        $relevant = [];
        $other = [];
        foreach ($sections as $section) {
            if (! $section['empty']) {
                continue;
            }

            if (preg_match($section['pattern'], $criteriaText)) {
                $relevant[] = $section['label'];
            } else {
                $other[] = $section['label'];
            }
        }

        return [...$relevant, ...$other];
    }

    private function validateCvInformation(mixed $data, string $cvText, string $profileText): array
    {
        if (! is_array($data)) {
            throw new EmployabilityAnalysisException('Gemini devolvió información del currículum en un formato inválido.');
        }

        $validated = [];
        foreach (self::CV_SECTIONS as $section) {
            $validated[$section] = $this->validateCitedItems($data[$section] ?? null, $cvText, $profileText);
        }

        return $validated;
    }

    private function validateCriteria(mixed $data, array $criteria, string $cvText, string $profileText): array
    {
        if (! is_array($data) || ! array_is_list($data) || count($data) !== count($criteria)) {
            throw new EmployabilityAnalysisException('Gemini no devolvió todos los criterios de la vacante.');
        }

        $byId = [];
        foreach ($data as $item) {
            if (! is_array($item)
                || ! is_string($item['id'] ?? null)
                || ! is_string($item['evidence'] ?? null)
                || mb_strlen($item['evidence']) > 500
                || ! in_array($item['match'] ?? null, ['direct', 'related', 'none'], true)
                || ! is_string($item['explanation'] ?? null)
                || trim($item['explanation']) === ''
                || mb_strlen($item['explanation']) > 500
                || array_key_exists($item['id'], $byId)) {
                throw new EmployabilityAnalysisException('Gemini devolvió criterios de comparación inválidos.');
            }
            $byId[$item['id']] = [
                'evidence' => trim($item['evidence']),
                'source' => $item['source'] ?? null,
                'match' => $item['match'],
                'explanation' => trim($item['explanation']),
            ];
        }

        $results = [];
        foreach ($criteria as $criterion) {
            if (! array_key_exists($criterion['id'], $byId)) {
                throw new EmployabilityAnalysisException('Gemini omitió un criterio de la vacante.');
            }

            $result = $byId[$criterion['id']];
            $evidence = $result['evidence'];
            $source = is_string($result['source']) ? $result['source'] : '';
            $evidenceValid = $evidence !== '' && $this->hasValidEvidence($evidence, $source, $cvText, $profileText);
            $match = $evidenceValid && $result['match'] !== 'none' ? $result['match'] : 'none';

            $results[] = [
                'id' => $criterion['id'],
                'requirement' => $criterion['text'],
                'evidence' => $match !== 'none' ? $evidence : '',
                'source' => $match !== 'none' ? $source : null,
                'match' => $match,
                'matched' => $match !== 'none',
                'explanation' => $result['explanation'],
            ];
        }

        return $results;
    }

    private function validateRecommendations(
        mixed $items,
        array $criteriaResults,
        string $cvText,
        string $profileText,
    ): array {
        if (! is_array($items) || ! array_is_list($items) || count($items) > 20) {
            throw new EmployabilityAnalysisException('Gemini devolvió recomendaciones en un formato inválido.');
        }

        $criteriaById = [];
        foreach ($criteriaResults as $criterion) {
            $criteriaById[$criterion['id']] = $criterion;
        }

        $results = [];
        foreach ($items as $item) {
            if (! is_array($item)
                || ! is_string($item['text'] ?? null)
                || ! in_array($item['kind'] ?? null, ['highlight', 'rephrase', 'gap'], true)
                || ! in_array($item['source'] ?? null, ['cv', 'perfil', 'vacante'], true)
                || ! is_string($item['criterion_id'] ?? null)
                || ! is_string($item['evidence'] ?? null)
                || trim($item['text']) === ''
                || mb_strlen($item['text']) > 1000
                || mb_strlen($item['evidence']) > 500) {
                throw new EmployabilityAnalysisException('Gemini devolvió una recomendación sin evidencia válida.');
            }

            $kind = $item['kind'];
            $source = $item['source'];
            $evidence = trim($item['evidence']);

            if ($kind === 'gap') {
                $criterion = $criteriaById[$item['criterion_id']] ?? null;
                if ($source !== 'vacante'
                    || $criterion === null
                    || $criterion['match'] !== 'none'
                    || ! $this->hasValidEvidence($evidence, 'vacante', '', '', $criterion['requirement'])) {
                    continue;
                }
            } elseif ($source === 'vacante'
                || ! $this->hasValidEvidence($evidence, $source, $cvText, $profileText)) {
                continue;
            }

            $results[] = [
                'kind' => $kind,
                'text' => $kind === 'gap'
                    ? 'La vacante solicita «'.$criterion['requirement'].'», pero no encontramos evidencia de este requisito en tu CV ni en tu perfil. Puedes adquirir esta experiencia o competencia y añadirla a tu CV únicamente si refleja tus antecedentes reales.'
                    : trim($item['text']),
                'source' => $source,
                'criterion_id' => $item['criterion_id'],
                'evidence' => $evidence,
            ];
        }

        return $results;
    }

    private function validateParagraph(mixed $paragraph, string $cvText, string $profileText): array
    {
        if (! is_array($paragraph)
            || ! is_string($paragraph['text'] ?? null)
            || mb_strlen($paragraph['text']) > 2000
            || ! is_array($paragraph['evidence'] ?? null)
            || ! array_is_list($paragraph['evidence'])
            || count($paragraph['evidence']) > 10) {
            throw new EmployabilityAnalysisException('Gemini devolvió un perfil profesional en un formato inválido.');
        }

        $evidence = [];
        foreach ($paragraph['evidence'] as $citation) {
            if (! is_array($citation)
                || ! is_string($citation['source'] ?? null)
                || ! is_string($citation['quote'] ?? null)
                || mb_strlen($citation['quote']) > 500) {
                continue;
            }

            $quote = trim($citation['quote']);
            if ($this->hasValidEvidence($quote, $citation['source'], $cvText, $profileText)) {
                $evidence[] = ['source' => $citation['source'], 'quote' => $quote];
            }
        }

        $text = trim($paragraph['text']);
        $verifiedQuotes = implode(' ', array_column($evidence, 'quote'));
        if ($text === '' || $evidence === [] || ! $this->claimIsSupportedByEvidence($text, $verifiedQuotes)) {
            return ['text' => '', 'evidence' => []];
        }

        return ['text' => $text, 'evidence' => $evidence];
    }

    private function validateAdaptedCv(mixed $adapted, string $cvText, string $profileText): array
    {
        if (! is_array($adapted)) {
            throw new EmployabilityAnalysisException('Gemini devolvió un currículum adaptado en un formato inválido.');
        }

        $result = [
            'profile' => $this->validateParagraph($adapted['profile'] ?? null, $cvText, $profileText),
        ];

        foreach (self::CV_SECTIONS as $section) {
            $result[$section] = $this->validateCitedItems($adapted[$section] ?? null, $cvText, $profileText);
        }

        return $result;
    }

    private function validateCitedItems(mixed $items, string $cvText, string $profileText): array
    {
        if (! is_array($items) || ! array_is_list($items) || count($items) > 50) {
            throw new EmployabilityAnalysisException('Gemini devolvió elementos del currículum en un formato inválido.');
        }

        $results = [];
        foreach ($items as $item) {
            if (! is_array($item)
                || ! is_string($item['text'] ?? null)
                || trim($item['text']) === ''
                || mb_strlen($item['text']) > 1500) {
                throw new EmployabilityAnalysisException('Gemini devolvió un dato del currículum en un formato inválido.');
            }

            $text = trim($item['text']);
            $evidence = is_string($item['evidence'] ?? null) && mb_strlen($item['evidence']) <= 500
                ? trim($item['evidence'])
                : '';
            $source = is_string($item['source'] ?? null) ? $item['source'] : '';
            $evidenceValid = $this->hasValidEvidence($evidence, $source, $cvText, $profileText)
                && $this->claimIsSupportedByEvidence($text, $evidence);
            $results[] = [
                'text' => $text,
                'evidence' => $evidenceValid ? $evidence : '',
                'source' => $evidenceValid ? $source : null,
                'evidence_valid' => $evidenceValid,
            ];
        }

        return $results;
    }

    private function hasValidEvidence(
        string $evidence,
        string $source,
        string $cvText,
        string $profileText,
        string $vacancyText = '',
    ): bool {
        $sourceText = match ($source) {
            'cv' => $cvText,
            'perfil' => $profileText,
            'vacante' => $vacancyText,
            default => '',
        };

        return $evidence !== ''
            && $sourceText !== ''
            && str_contains($sourceText, $evidence);
    }

    private function claimIsSupportedByEvidence(string $claim, string $evidence): bool
    {
        $claimWords = $this->meaningfulWords($claim);
        $evidenceWords = $this->meaningfulWords($evidence);

        return $claimWords !== [] && array_diff($claimWords, $evidenceWords) === [];
    }

    private function meaningfulWords(string $text): array
    {
        preg_match_all('/[\p{L}\p{N}]+/u', mb_strtolower($text), $matches);

        return array_values(array_diff(array_unique($matches[0]), [
            'a', 'al', 'con', 'de', 'del', 'el', 'en', 'la', 'las', 'lo', 'los',
            'para', 'por', 'un', 'una', 'y', 'conocimientos', 'conocimiento',
            'perfil', 'profesional', 'documentado', 'documentada', 'documentados',
            'documentadas',
        ]));
    }
}
