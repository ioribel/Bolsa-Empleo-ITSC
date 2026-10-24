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
    ];

    public function __construct(
        private readonly CvTextExtractor $extractor,
        private readonly CvPrivacyRedactor $redactor,
        private readonly VacancyCriteriaBuilder $criteriaBuilder,
        private readonly GeminiEmployabilityService $gemini,
    ) {}

    public function analyze(Estudiante $estudiante, Vacante $vacante, ?string $cvContents = null): array
    {
        $cvContents ??= $this->getCvContents($estudiante);
        $originalText = $this->extractor->extract($cvContents);
        $cvText = $this->redactor->redact($originalText, $estudiante);
        $criteria = $this->criteriaBuilder->build($vacante);
        $raw = $this->gemini->analyze($cvText, $vacante, $criteria);

        $cvInformation = $this->validateCvInformation($raw['cv_information'] ?? null, $cvText);
        $criteriaResults = $this->validateCriteria($raw['criteria'] ?? null, $criteria, $cvText);
        $recommendations = $this->validateRecommendations(
            $raw['recommendations'] ?? null,
            $cvText,
            $this->vacancyEvidenceText($vacante),
        );
        $suggestedProfile = $this->validateParagraph($raw['suggested_profile'] ?? null, $cvText);
        $adaptedCv = $this->validateAdaptedCv($raw['adapted_cv'] ?? null, $cvText);

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
            'cv_fingerprint' => hash('sha256', $cvContents),
        ];
    }

    public function validateAdaptationSubmission(array $draft, string $cvText): array
    {
        $adapted = $this->validateAdaptedCv($draft, $cvText);

        if ($adapted['profile']['text'] === '') {
            throw new EmployabilityAnalysisException('El perfil profesional no puede quedar vacío.');
        }

        foreach (self::CV_SECTIONS as $section) {
            $adapted[$section] = array_values(array_map(
                fn (array $item): array => ['text' => $item['text'], 'evidence' => $item['evidence']],
                array_filter($adapted[$section], fn (array $item): bool => $item['evidence_valid']),
            ));
        }

        return $adapted;
    }

    private function getCvContents(Estudiante $estudiante): string
    {
        if (! $estudiante->cv_path) {
            throw new CvExtractionException('Primero carga un currículum PDF en tu perfil.');
        }

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($estudiante->cv_path)) {
                return Storage::disk($disk)->get($estudiante->cv_path);
            }
        }

        throw new CvExtractionException('El archivo del currículum no está disponible. Vuelve a cargarlo desde tu perfil.');
    }

    private function validateCvInformation(mixed $data, string $cvText): array
    {
        if (! is_array($data)) {
            throw new EmployabilityAnalysisException('Gemini devolvió información del currículum en un formato inválido.');
        }

        $validated = [];
        foreach (self::CV_SECTIONS as $section) {
            $validated[$section] = $this->validateCitedItems($data[$section] ?? null, $cvText);
        }

        return $validated;
    }

    private function validateCriteria(mixed $data, array $criteria, string $cvText): array
    {
        if (! is_array($data) || ! array_is_list($data) || count($data) !== count($criteria)) {
            throw new EmployabilityAnalysisException('Gemini no devolvió todos los criterios de la vacante.');
        }

        $byId = [];
        foreach ($data as $item) {
            if (! is_array($item)
                || ! is_string($item['id'] ?? null)
                || ! is_string($item['cv_evidence'] ?? null)
                || mb_strlen($item['cv_evidence']) > 500
                || ! in_array($item['match'] ?? null, ['direct', 'related', 'none'], true)
                || ! is_string($item['explanation'] ?? null)
                || trim($item['explanation']) === ''
                || mb_strlen($item['explanation']) > 500
                || array_key_exists($item['id'], $byId)) {
                throw new EmployabilityAnalysisException('Gemini devolvió criterios de comparación inválidos.');
            }
            $byId[$item['id']] = [
                'evidence' => trim($item['cv_evidence']),
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
            if ($evidence !== '') {
                $this->assertEvidence($evidence, $cvText);
            }
            $match = $evidence === '' ? 'none' : $result['match'];
            $matched = $match !== 'none';

            $results[] = [
                'id' => $criterion['id'],
                'requirement' => $criterion['text'],
                'evidence' => $matched ? $evidence : '',
                'match' => $match,
                'matched' => $matched,
                'explanation' => $result['explanation'],
            ];
        }

        return $results;
    }

    private function validateRecommendations(mixed $items, string $cvText, string $vacancyText): array
    {
        if (! is_array($items) || ! array_is_list($items) || count($items) > 20) {
            throw new EmployabilityAnalysisException('Gemini devolvió recomendaciones en un formato inválido.');
        }

        $results = [];
        foreach ($items as $item) {
            if (! is_array($item)
                || ! is_string($item['text'] ?? null)
                || ! is_string($item['source'] ?? null)
                || ! in_array($item['source'], ['cv', 'vacante'], true)
                || ! is_string($item['evidence'] ?? null)
                || trim($item['text']) === ''
                || mb_strlen($item['text']) > 1000
                || mb_strlen($item['evidence']) > 500) {
                throw new EmployabilityAnalysisException('Gemini devolvió una recomendación sin evidencia válida.');
            }

            $source = $item['source'] === 'cv' ? $cvText : $vacancyText;
            $evidence = trim($item['evidence']);
            $this->assertEvidence($evidence, $source);

            $results[] = [
                'text' => trim($item['text']),
                'source' => $item['source'],
                'evidence' => $evidence,
            ];
        }

        return $results;
    }

    private function validateParagraph(mixed $paragraph, string $cvText): array
    {
        if (! is_array($paragraph)
            || ! is_string($paragraph['text'] ?? null)
            || mb_strlen($paragraph['text']) > 2000
            || ! is_array($paragraph['evidence'] ?? null)
            || ! array_is_list($paragraph['evidence'])
            || count($paragraph['evidence']) > 10) {
            throw new EmployabilityAnalysisException('Gemini devolvió un perfil profesional en un formato inválido.');
        }

        $evidence = array_map(function (mixed $quote) use ($cvText): string {
            if (! is_string($quote) || mb_strlen($quote) > 500) {
                throw new EmployabilityAnalysisException('Gemini devolvió evidencia inválida.');
            }

            $quote = trim($quote);
            $this->assertEvidence($quote, $cvText);

            return $quote;
        }, $paragraph['evidence']);

        $text = trim($paragraph['text']);
        if (($text === '') !== ($evidence === [])) {
            throw new EmployabilityAnalysisException('El perfil profesional no está respaldado por evidencia suficiente.');
        }

        return ['text' => $text, 'evidence' => $evidence];
    }

    private function validateAdaptedCv(mixed $adapted, string $cvText): array
    {
        if (! is_array($adapted)) {
            throw new EmployabilityAnalysisException('Gemini devolvió un currículum adaptado en un formato inválido.');
        }

        $result = [
            'profile' => $this->validateParagraph($adapted['profile'] ?? null, $cvText),
        ];

        foreach (self::CV_SECTIONS as $section) {
            $result[$section] = $this->validateCitedItems($adapted[$section] ?? null, $cvText);
        }

        return $result;
    }

    private function validateCitedItems(mixed $items, string $cvText): array
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
            $evidenceValid = $this->hasValidEvidence($evidence, $cvText);
            $results[] = [
                'text' => $text,
                'evidence' => $evidenceValid ? $evidence : '',
                'evidence_valid' => $evidenceValid,
            ];
        }

        return $results;
    }

    private function hasValidEvidence(string $evidence, string $source): bool
    {
        return $evidence !== '' && str_contains($this->normalize($source), $this->normalize($evidence));
    }

    private function assertEvidence(string $evidence, string $source): void
    {
        if (! $this->hasValidEvidence($evidence, $source)) {
            throw new EmployabilityAnalysisException('Gemini devolvió una afirmación que no se pudo verificar en el documento.');
        }
    }

    private function normalize(string $text): string
    {
        $text = preg_replace('/\s+/u', ' ', mb_strtolower($text));

        return trim($text);
    }

    private function vacancyEvidenceText(Vacante $vacante): string
    {
        return implode(' ', array_filter([
            $vacante->titulo,
            $vacante->descripcion,
            $vacante->requisitos,
        ]));
    }
}
