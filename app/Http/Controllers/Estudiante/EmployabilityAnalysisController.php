<?php

namespace App\Http\Controllers\Estudiante;

use App\Exceptions\CvExtractionException;
use App\Exceptions\EmployabilityAnalysisException;
use App\Http\Controllers\Controller;
use App\Models\Vacante;
use App\Services\Employability\CvPrivacyRedactor;
use App\Services\Employability\CvTextExtractor;
use App\Services\Employability\EmployabilityAnalysisService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployabilityAnalysisController extends Controller
{
    public function analyze(Request $request, string $vacanteId, EmployabilityAnalysisService $analysisService): Response|RedirectResponse
    {
        $request->validate([
            'consent' => ['required', 'accepted'],
            'cv' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        $estudiante = $request->user()->estudiante;
        $vacante = Vacante::where('estado', 'publicada')
            ->with(['empresa', 'carreras'])
            ->findOrFail($vacanteId);

        abort_unless($vacante->carreras->contains('id', $estudiante->carrera_id), 403);

        try {
            $uploadedCv = $request->file('cv');
            $cvContents = $uploadedCv?->get();
            $analysis = $analysisService->analyze($estudiante, $vacante, $cvContents);

            if ($cvContents !== null) {
                $cvSource = bin2hex(random_bytes(32));
                Cache::store('file')->put($this->uploadedCvCacheKey($estudiante->user_id, $cvSource), base64_encode($cvContents), now()->addMinutes(30));
                $analysis['cv_source'] = $cvSource;
            }
        } catch (CvExtractionException|EmployabilityAnalysisException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return response()
            ->view('estudiante.vacantes.analysis', compact('analysis', 'vacante'))
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function downloadAdaptedCv(
        Request $request,
        string $vacanteId,
        CvTextExtractor $extractor,
        CvPrivacyRedactor $redactor,
        EmployabilityAnalysisService $analysisService,
    ) {
        $validated = $request->validate([
            'consent_confirmed' => ['required', 'accepted'],
            'cv_fingerprint' => ['required', 'regex:/\A[a-f0-9]{64}\z/'],
            'adapted_cv.profile.text' => ['required', 'string', 'max:2000'],
            'adapted_cv.profile.evidence' => ['required', 'array', 'min:1', 'max:10'],
            'adapted_cv.profile.evidence.*' => ['required', 'string', 'max:500'],
            'cv_source' => ['nullable', 'regex:/\A[a-f0-9]{64}\z/'],
            ...$this->sectionValidationRules(),
        ]);

        $estudiante = $request->user()->estudiante;
        $vacante = Vacante::with(['empresa', 'carreras'])->findOrFail($vacanteId);
        abort_unless($vacante->carreras->contains('id', $estudiante->carrera_id), 403);

        try {
            $cvSource = $validated['cv_source'] ?? null;
            if ($cvSource === null) {
                $contents = $this->profileCvContents($estudiante->cv_path);
            } else {
                $encodedContents = Cache::store('file')->get($this->uploadedCvCacheKey($estudiante->user_id, $cvSource));
                $contents = is_string($encodedContents) ? base64_decode($encodedContents, true) : false;
            }

            abort_if(! is_string($contents), 409, 'El PDF temporal ya no está disponible. Vuelve a analizar la vacante.');
            abort_unless(hash_equals($validated['cv_fingerprint'], hash('sha256', $contents)), 409, 'El currículum cambió desde el análisis. Vuelve a analizar la vacante.');
            $cvText = $redactor->redact($extractor->extract($contents), $estudiante);
            $draft = $validated['adapted_cv'];

            foreach (['education', 'skills', 'experience', 'projects', 'certifications', 'technologies'] as $section) {
                $draft[$section] ??= [];
            }
            $adaptedCv = $analysisService->validateAdaptationSubmission($draft, $cvText);
        } catch (CvExtractionException|EmployabilityAnalysisException $exception) {
            abort(422, $exception->getMessage());
        }

        $pdf = Pdf::loadView('estudiante.vacantes.adapted-cv', [
            'adaptedCv' => $adaptedCv,
            'student' => $estudiante->load('user', 'carrera'),
            'vacante' => $vacante,
        ])->setPaper('a4')->download('cv-adaptado-vacante-'.$vacante->id.'.pdf');

        if ($cvSource !== null) {
            Cache::store('file')->forget($this->uploadedCvCacheKey($estudiante->user_id, $cvSource));
        }

        return $pdf->withHeaders(['Cache-Control' => 'private, no-store, max-age=0']);
    }

    private function profileCvContents(?string $path): string
    {
        abort_unless($path, 422, 'No hay un currículum disponible en tu perfil.');

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->get($path);
            }
        }

        abort(422, 'No se encontró el currículum. Vuelve a cargarlo desde tu perfil.');
    }

    private function sectionValidationRules(): array
    {
        $rules = [];
        foreach (['education', 'skills', 'experience', 'projects', 'certifications', 'technologies'] as $section) {
            $rules["adapted_cv.{$section}"] = ['sometimes', 'array', 'max:50'];
            $rules["adapted_cv.{$section}.*.text"] = ['required', 'string', 'max:1000'];
            $rules["adapted_cv.{$section}.*.evidence"] = ['required', 'string', 'max:500'];
        }

        return $rules;
    }

    private function uploadedCvCacheKey(int|string $userId, string $cvSource): string
    {
        return 'cv-analysis:'.$userId.':'.$cvSource;
    }
}
