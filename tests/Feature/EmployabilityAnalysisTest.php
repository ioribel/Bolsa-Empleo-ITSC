<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\EstudianteExperiencia;
use App\Models\EstudianteFormacion;
use App\Models\Habilidad;
use App\Models\User;
use App\Models\Vacante;
use App\Http\Controllers\Estudiante\EmployabilityAnalysisController;
use App\Services\Employability\CvTextExtractor;
use Dompdf\Dompdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployabilityAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_analyze_cv_with_fake_gemini_and_get_verifiable_score(): void
    {
        config([
            'services.gemini.key' => 'fake-test-key',
            'services.gemini.model' => 'gemini-2.5-flash',
        ]);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse(), 200)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $pdf = $this->pdfWithText('PHP Laravel desarrollo de software');
        $student->update([
            'cv_path' => 'cvs/perfiles/cv.pdf',
            'cv_original_name' => 'cv.pdf',
            'telefono' => '+1 809 555 1234',
            'titular' => 'Desarrollo de software',
            'resumen' => 'Estudiante de Prueba estudiante@example.test +1 809 555 1234 MATRICULA-TEST-01 https://perfil.example.com',
        ]);
        Storage::disk('local')->put($student->cv_path, $pdf);

        $response = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.analizar-cv', $vacancy),
            $this->consentData(),
        );

        $response->assertOk()
            ->assertSee('50.0%')
            ->assertSee('Coincidencia directa')
            ->assertSee('No identificado en CV ni perfil')
            ->assertSee('Descargar CV adaptado en PDF');
        $analysis = $response->viewData('analysis');
        $this->assertSame('direct', $analysis['criteria'][0]['match']);
        $this->assertSame('none', $analysis['criteria'][1]['match']);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));

        Http::assertSent(function (ClientRequest $request) use ($studentUser, $student): bool {
            $body = json_encode($request->data(), JSON_UNESCAPED_UNICODE);

            return $request->hasHeader('x-goog-api-key', 'fake-test-key')
                && str_contains($request->url(), '/models/gemini-2.5-flash:generateContent')
                && str_contains($body, '[CV]')
                && str_contains($body, '[PERFIL]')
                && ! str_contains($body, $studentUser->name)
                && ! str_contains($body, $studentUser->email)
                && ! str_contains($body, $student->matricula)
                && ! str_contains($body, $student->telefono)
                && ! str_contains($body, 'https://perfil.example.com');
        });
    }

    public function test_consent_is_required_before_contacting_gemini(): void
    {
        Http::fake();
        [$studentUser, $student, $vacancy] = $this->createContext();
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::fake('local');
        Storage::disk('local')->put($student->cv_path, $this->pdfWithText('PHP Laravel'));

        $this->actingAs($studentUser)
            ->from(route('estudiante.vacantes.show', $vacancy))
            ->post(route('estudiante.vacantes.analizar-cv', $vacancy), ['consent_version' => EmployabilityAnalysisController::CONSENT_VERSION])
            ->assertSessionHasErrors('consent');

        Http::assertNothingSent();
    }

    public function test_cv_processing_modal_is_hidden_when_vacancy_page_loads(): void
    {
        [$studentUser, , $vacancy] = $this->createContext();

        $this->actingAs($studentUser)
            ->get(route('estudiante.vacantes.show', $vacancy))
            ->assertOk()
            ->assertSee('id="cv-processing-modal"', false)
            ->assertSee('id="cv-processing-modal" class="hidden"', false)
            ->assertSee('align-items: center; justify-content: center;', false);
    }

    public function test_analysis_can_read_existing_cv_stored_on_the_legacy_public_disk(): void
    {
        config([
            'services.gemini.key' => 'fake-test-key',
            'services.gemini.model' => 'gemini-2.5-flash',
        ]);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse(), 200)]);
        Storage::fake('local');
        Storage::fake('public');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $student->update(['cv_path' => 'cvs/perfiles/cv-antiguo.pdf']);
        Storage::disk('public')->put($student->cv_path, $this->pdfWithText('PHP Laravel desarrollo de software'));

        $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.analizar-cv', $vacancy),
            $this->consentData(),
        )->assertOk()->assertSee('50.0%');
    }

    public function test_student_can_analyze_an_uploaded_cv_without_replacing_the_profile_cv_and_download_from_it(): void
    {
        config([
            'services.gemini.key' => 'fake-test-key',
            'services.gemini.model' => 'gemini-2.5-flash',
        ]);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse(), 200)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $profilePdf = $this->pdfWithText('Experiencia confidencial en COBOL');
        $uploadedPdf = $this->pdfWithText('CV alternativo con PHP Laravel');
        $student->update(['cv_path' => 'cvs/perfiles/perfil.pdf']);
        Storage::disk('local')->put($student->cv_path, $profilePdf);

        $analysisResponse = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.analizar-cv', $vacancy),
            [
                ...$this->consentData(),
                'cv' => UploadedFile::fake()->createWithContent('cv-alternativo.pdf', $uploadedPdf),
            ],
        );

        $analysisResponse->assertOk();
        $analysis = $analysisResponse->viewData('analysis');
        $this->assertSame(hash('sha256', $uploadedPdf), $analysis['cv_fingerprint']);
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $analysis['cv_source']);
        $this->assertSame(
            base64_encode($uploadedPdf),
            Cache::store('file')->get('cv-analysis:'.$student->user_id.':'.$analysis['cv_source']),
        );
        $this->assertSame($profilePdf, Storage::disk('local')->get($student->cv_path));
        Http::assertSent(function (ClientRequest $request): bool {
            $body = json_encode($request->data(), JSON_UNESCAPED_UNICODE);

            return str_contains($body, 'CV alternativo con PHP Laravel')
                && ! str_contains($body, 'Experiencia confidencial en COBOL');
        });

        $citation = 'PHP Laravel';
        $downloadResponse = $this->post(
            route('estudiante.vacantes.cv-adaptado', $vacancy),
            [
                'consent_confirmed' => '1',
                'cv_fingerprint' => $analysis['cv_fingerprint'],
                'cv_source' => $analysis['cv_source'],
                'profile_only' => '0',
                'adapted_cv' => [
                    'profile' => [
                        'text' => 'Conocimientos de PHP y Laravel.',
                        'evidence' => [['source' => 'cv', 'quote' => $citation]],
                    ],
                    'skills' => [
                        ['text' => 'PHP y Laravel', 'evidence' => $citation, 'source' => 'cv'],
                    ],
                ],
            ],
        );

        $downloadResponse->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_analysis_shows_cv_items_without_valid_evidence_as_unverified(): void
    {
        $payload = $this->geminiPayload();
        unset($payload['cv_information']['skills'][0]['evidence']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->wrapGemini($payload), 200)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('local')->put($student->cv_path, $this->pdfWithText('PHP Laravel desarrollo de software'));

        $response = $this->actingAs($studentUser)
            ->post(route('estudiante.vacantes.analizar-cv', $vacancy), $this->consentData())
            ->assertOk()
            ->assertSee('Sin evidencia verificable')
            ->assertSee('PHP y Laravel');

        $analysis = $response->viewData('analysis');
        $this->assertFalse($analysis['cv_information']['skills'][0]['evidence_valid']);
        $this->assertSame('', $analysis['cv_information']['skills'][0]['evidence']);
    }

    public function test_semantically_related_skill_is_returned_as_partial_match(): void
    {
        config([
            'services.gemini.key' => 'fake-test-key',
            'services.gemini.model' => 'gemini-2.5-flash',
        ]);
        $payload = $this->geminiPayload();
        $payload['criteria'] = [[
            'id' => 'R1',
            'evidence' => 'Organización y control de registros y documentos',
            'source' => 'cv',
            'match' => 'related',
            'explanation' => 'La evidencia respalda la organización y el manejo documental, pero no demuestra explícitamente la atención al detalle.',
        ]];
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->wrapGemini($payload), 200)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $vacancy->update(['requisitos' => 'Organización y atención a los detalles.']);
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('local')->put(
            $student->cv_path,
            $this->pdfWithText('Organización y control de registros y documentos. PHP Laravel'),
        );

        $response = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.analizar-cv', $vacancy),
            $this->consentData(),
        );

        $response->assertOk()
            ->assertSee('Coincidencia relacionada')
            ->assertSee('La evidencia respalda la organización', false)
            ->assertSee('50.0%');
        $analysis = $response->viewData('analysis');
        $this->assertSame('related', $analysis['criteria'][0]['match']);
        $this->assertSame('Organización y control de registros y documentos', $analysis['criteria'][0]['evidence']);
        $this->assertSame(50.0, $analysis['compatibility_percentage']);
    }

    public function test_unverified_ai_items_are_not_preloaded_but_student_can_confirm_a_manual_edit(): void
    {
        $payload = $this->geminiPayload();
        $payload['adapted_cv']['skills'][0]['evidence'] = 'C++ experto';
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->wrapGemini($payload), 200)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $cvContents = $this->pdfWithText('PHP Laravel desarrollo de software');
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('local')->put($student->cv_path, $cvContents);

        $analysisResponse = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.analizar-cv', $vacancy),
            $this->consentData(),
        );
        $analysisResponse->assertOk();
        $analysis = $analysisResponse->viewData('analysis');
        $this->assertTrue($analysis['adapted_cv']['skills'][0]['evidence_valid']);
        $analysisResponse->assertDontSee('Experto en C++');

        $downloadResponse = $this->post(
            route('estudiante.vacantes.cv-adaptado', $vacancy),
            [
                'consent_confirmed' => '1',
                'cv_fingerprint' => hash('sha256', $cvContents),
                'profile_only' => '0',
                'adapted_cv' => [
                    'profile' => [
                        'text' => 'Conocimientos de PHP y Laravel.',
                        'evidence' => [['source' => 'cv', 'quote' => 'PHP Laravel']],
                    ],
                    'skills' => [
                        ['text' => 'Experto en C++', 'evidence' => 'C++ experto', 'source' => 'cv'],
                    ],
                ],
            ],
        );

        $downloadResponse->assertOk()->assertHeader('content-type', 'application/pdf');
        $pdfText = (new \Smalot\PdfParser\Parser)->parseContent($downloadResponse->getContent())->getText();
        $this->assertStringContainsString('Experto en C++', $pdfText);
        $this->assertStringContainsString('El estudiante confirmó como verdadero', $pdfText);
    }

    public function test_verified_cv_information_populates_adapted_draft_when_gemini_draft_is_empty(): void
    {
        config(['services.gemini.key' => 'fake-test-key', 'services.gemini.model' => 'gemini-2.5-flash']);
        $payload = $this->geminiPayload();
        $payload['adapted_cv'] = [
            'profile' => ['text' => '', 'evidence' => []],
            'education' => [],
            'skills' => [],
            'experience' => [],
            'projects' => [],
            'certifications' => [],
            'technologies' => [],
            'languages' => [],
        ];
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->wrapGemini($payload), 200)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $cvContents = $this->pdfWithText('PHP Laravel desarrollo de software');
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('local')->put($student->cv_path, $cvContents);

        $response = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.analizar-cv', $vacancy),
            $this->consentData(),
        );

        $response->assertOk()->assertSee('Descargar CV adaptado en PDF');
        $analysis = $response->viewData('analysis');
        $this->assertSame('PHP y Laravel', $analysis['adapted_cv']['skills'][0]['text']);
        $this->assertTrue($analysis['adapted_cv']['skills'][0]['evidence_valid']);
        $this->assertSame('PHP y Laravel', $analysis['adapted_cv']['profile']['text']);
    }

    public function test_editor_and_download_remain_available_when_no_ai_content_is_verifiable(): void
    {
        config(['services.gemini.key' => 'fake-test-key', 'services.gemini.model' => 'gemini-2.5-flash']);
        $payload = $this->geminiPayload();
        foreach (array_keys($payload['cv_information']) as $section) {
            $payload['cv_information'][$section] = [];
        }
        foreach (array_keys($payload['adapted_cv']) as $section) {
            $payload['adapted_cv'][$section] = $section === 'profile'
                ? ['text' => '', 'evidence' => []]
                : [];
        }
        $payload['criteria'] = [
            ['id' => 'R1', 'evidence' => '', 'source' => 'none', 'match' => 'none', 'explanation' => 'Sin evidencia.'],
            ['id' => 'R2', 'evidence' => '', 'source' => 'none', 'match' => 'none', 'explanation' => 'Sin evidencia.'],
        ];
        $payload['recommendations'] = [];
        $payload['suggested_profile'] = ['text' => '', 'evidence' => []];
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->wrapGemini($payload), 200)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $cvContents = $this->pdfWithText('Texto de CV sin datos profesionales detectables.');
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('local')->put($student->cv_path, $cvContents);

        $response = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.analizar-cv', $vacancy),
            $this->consentData(),
        );

        $response->assertOk()
            ->assertSee('No se generó un perfil verificable')
            ->assertSee('name="adapted_cv[manual][skills]"', false)
            ->assertSee('Descargar CV adaptado en PDF');

        $downloadResponse = $this->post(
            route('estudiante.vacantes.cv-adaptado', $vacancy),
            [
                'consent_confirmed' => '1',
                'cv_fingerprint' => hash('sha256', $cvContents),
                'profile_only' => '0',
                'adapted_cv' => [
                    'profile' => ['text' => 'Estudiante con interés en desarrollo web.', 'evidence' => []],
                    'manual' => ['skills' => 'PHP y Laravel'],
                ],
            ],
        );

        $downloadResponse->assertOk()->assertHeader('content-type', 'application/pdf');
        $pdfText = (new \Smalot\PdfParser\Parser)->parseContent($downloadResponse->getContent())->getText();
        $this->assertStringContainsString('Estudiante con interés en desarrollo web.', $pdfText);
        $this->assertStringContainsString('PHP y Laravel', $pdfText);
        $this->assertStringContainsString('El estudiante confirmó como verdadero', $pdfText);
    }

    public function test_gemini_rate_limit_is_reported_without_showing_provider_response(): void
    {
        config(['services.gemini.key' => 'fake-test-key', 'services.gemini.model' => 'gemini-2.5-flash']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => 'private provider details'], 429)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('local')->put($student->cv_path, $this->pdfWithText('PHP Laravel'));

        $this->actingAs($studentUser)
            ->from(route('estudiante.vacantes.show', $vacancy))
            ->post(route('estudiante.vacantes.analizar-cv', $vacancy), $this->consentData())
            ->assertRedirect(route('estudiante.vacantes.show', $vacancy))
            ->assertSessionHas('error', 'El servicio de IA alcanzó su límite temporal. Espera unos minutos e inténtalo nuevamente.');
    }

    public function test_unexpected_gemini_json_is_reported_as_a_safe_error(): void
    {
        config(['services.gemini.key' => 'fake-test-key', 'services.gemini.model' => 'gemini-2.5-flash']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'not valid json']]]]],
        ], 200)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('local')->put($student->cv_path, $this->pdfWithText('PHP Laravel'));

        $this->actingAs($studentUser)
            ->from(route('estudiante.vacantes.show', $vacancy))
            ->post(route('estudiante.vacantes.analizar-cv', $vacancy), $this->consentData())
            ->assertRedirect(route('estudiante.vacantes.show', $vacancy))
            ->assertSessionHas('error', 'Gemini devolvió un resultado que no se pudo validar.');
    }

    public function test_student_can_download_adapted_cv_from_a_legacy_public_profile_cv(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $pdfContents = $this->pdfWithText('PHP Laravel desarrollo de software');
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('public')->put($student->cv_path, $pdfContents);
        $fingerprint = hash('sha256', $pdfContents);
        $evidence = (new CvTextExtractor)->extract($pdfContents);
        $citation = str_contains($evidence, 'PHP Laravel')
            ? 'PHP Laravel'
            : $evidence;

        $response = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.cv-adaptado', $vacancy),
            [
                'consent_confirmed' => '1',
                'cv_fingerprint' => $fingerprint,
                'profile_only' => '0',
                'adapted_cv' => [
                    'profile' => [
                        'text' => 'Conocimientos de PHP y Laravel.',
                        'evidence' => [['source' => 'cv', 'quote' => $citation]],
                    ],
                    'skills' => [
                        ['text' => 'PHP y Laravel', 'evidence' => $citation, 'source' => 'cv'],
                    ],
                ],
            ],
        );

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_analysis_is_forbidden_when_vacancy_is_not_for_student_career(): void
    {
        Http::fake();
        [$studentUser, , $vacancy] = $this->createContext();
        $otherCareer = Carrera::create(['nombre' => 'Administración']);
        $vacancy->carreras()->sync([$otherCareer->id]);

        $this->actingAs($studentUser)
            ->post(route('estudiante.vacantes.analizar-cv', $vacancy), $this->consentData())
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_profile_citation_is_verified_and_shown_with_its_source(): void
    {
        config(['services.gemini.key' => 'fake-test-key', 'services.gemini.model' => 'gemini-2.5-flash']);
        $payload = $this->geminiPayload();
        $payload['cv_information']['skills'] = [[
            'text' => 'Excel avanzado',
            'evidence' => 'Excel avanzado',
            'source' => 'perfil',
        ]];
        $payload['criteria'][0] = [
            'id' => 'R1',
            'evidence' => 'Excel avanzado',
            'source' => 'perfil',
            'match' => 'direct',
            'explanation' => 'El perfil incluye esta habilidad.',
        ];
        $payload['suggested_profile']['evidence'] = [['source' => 'perfil', 'quote' => 'Excel avanzado']];
        $payload['adapted_cv']['profile']['evidence'] = [['source' => 'perfil', 'quote' => 'Excel avanzado']];
        $payload['adapted_cv']['skills'] = [[
            'text' => 'Excel avanzado',
            'evidence' => 'Excel avanzado',
            'source' => 'perfil',
        ]];
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->wrapGemini($payload), 200)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $this->addSufficientProfile($student);
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('local')->put($student->cv_path, $this->pdfWithText('PHP Laravel'));

        $response = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.analizar-cv', $vacancy),
            $this->consentData(),
        );

        $response->assertOk()->assertSee('Perfil');
        $analysis = $response->viewData('analysis');
        $this->assertSame('perfil', $analysis['criteria'][0]['source']);
        $this->assertSame('Excel avanzado', $analysis['criteria'][0]['evidence']);
        $this->assertTrue($analysis['cv_information']['skills'][0]['evidence_valid']);
        $this->assertSame('perfil', $analysis['cv_information']['skills'][0]['source']);
    }

    public function test_citations_absent_from_the_declared_source_are_discarded(): void
    {
        config(['services.gemini.key' => 'fake-test-key', 'services.gemini.model' => 'gemini-2.5-flash']);
        $payload = $this->geminiPayload();
        $payload['cv_information']['skills'][0]['evidence'] = 'Kubernetes avanzado';
        $payload['criteria'][0] = [
            'id' => 'R1',
            'evidence' => 'PHP Laravel',
            'source' => 'perfil',
            'match' => 'direct',
            'explanation' => 'El modelo atribuye la cita al perfil.',
        ];
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->wrapGemini($payload), 200)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('local')->put($student->cv_path, $this->pdfWithText('PHP Laravel'));

        $response = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.analizar-cv', $vacancy),
            $this->consentData(),
        );

        $response->assertOk();
        $analysis = $response->viewData('analysis');
        $this->assertSame('none', $analysis['criteria'][0]['match']);
        $this->assertSame('', $analysis['criteria'][0]['evidence']);
        $this->assertFalse($analysis['cv_information']['skills'][0]['evidence_valid']);
    }

    public function test_profile_only_analysis_works_when_profile_meets_the_minimum_threshold(): void
    {
        config(['services.gemini.key' => 'fake-test-key', 'services.gemini.model' => 'gemini-2.5-flash']);
        $payload = $this->geminiPayload();
        $payload['cv_information']['skills'] = [[
            'text' => 'Excel avanzado',
            'evidence' => 'Excel avanzado',
            'source' => 'perfil',
        ]];
        $payload['criteria'][0] = [
            'id' => 'R1',
            'evidence' => 'Excel avanzado',
            'source' => 'perfil',
            'match' => 'direct',
            'explanation' => 'Habilidad registrada en el perfil.',
        ];
        $payload['suggested_profile']['evidence'] = [['source' => 'perfil', 'quote' => 'Excel avanzado']];
        $payload['adapted_cv']['profile']['evidence'] = [['source' => 'perfil', 'quote' => 'Excel avanzado']];
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->wrapGemini($payload), 200)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $this->addSufficientProfile($student);
        $student->update(['cv_path' => 'cvs/perfiles/ilegible.pdf']);
        Storage::disk('local')->put($student->cv_path, '%PDF-1.4 corrupto');

        $this->actingAs($studentUser)
            ->get(route('estudiante.vacantes.show', $vacancy))
            ->assertOk()
            ->assertSee('opcional')
            ->assertSee('Si ese CV no es legible');

        $response = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.analizar-cv', $vacancy),
            $this->consentData(),
        );

        $response->assertOk()->assertSee('únicamente con la información verificada de tu perfil');
        $analysis = $response->viewData('analysis');
        $this->assertTrue($analysis['profile_only']);
        Http::assertSent(function (ClientRequest $request): bool {
            $prompt = data_get($request->data(), 'contents.0.parts.0.text');

            return is_string($prompt)
                && str_contains($prompt, "[CV]\n\n")
                && str_contains($prompt, '[PERFIL]')
                && str_contains($prompt, 'Habilidad: Excel avanzado');
        });

        $downloadResponse = $this->post(
            route('estudiante.vacantes.cv-adaptado', $vacancy),
            [
                'consent_confirmed' => '1',
                'cv_fingerprint' => $analysis['cv_fingerprint'],
                'profile_only' => '1',
                'adapted_cv' => [
                    'profile' => [
                        'text' => 'Conocimientos de Excel avanzado.',
                        'evidence' => [['source' => 'perfil', 'quote' => 'Excel avanzado']],
                    ],
                    'skills' => [
                        ['text' => 'Excel avanzado', 'evidence' => 'Excel avanzado', 'source' => 'perfil'],
                    ],
                ],
            ],
        );
        $downloadResponse->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_profile_only_analysis_without_sufficient_profile_does_not_call_gemini(): void
    {
        Http::fake();
        [$studentUser, , $vacancy] = $this->createContext();

        $this->actingAs($studentUser)
            ->from(route('estudiante.vacantes.show', $vacancy))
            ->post(route('estudiante.vacantes.analizar-cv', $vacancy), $this->consentData())
            ->assertRedirect(route('estudiante.vacantes.show', $vacancy))
            ->assertSessionHas('error', 'No hay un CV legible ni suficiente información profesional para analizar. Completa tu perfil (agrega una experiencia o una formación y al menos tres habilidades) o sube un CV PDF legible.');

        Http::assertNothingSent();
    }

    public function test_profile_skill_is_verified_and_student_confirmed_edits_are_identified(): void
    {
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $this->addSufficientProfile($student);
        $cvContents = $this->pdfWithText('PHP Laravel desarrollo de software');
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('local')->put($student->cv_path, $cvContents);
        $profileCitation = 'Excel avanzado';

        $validResponse = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.cv-adaptado', $vacancy),
            [
                'consent_confirmed' => '1',
                'cv_fingerprint' => hash('sha256', $cvContents),
                'profile_only' => '0',
                'adapted_cv' => [
                    'profile' => [
                        'text' => 'Conocimientos de Excel avanzado.',
                        'evidence' => [['source' => 'perfil', 'quote' => $profileCitation]],
                    ],
                    'skills' => [
                        ['text' => 'Excel avanzado', 'evidence' => $profileCitation, 'source' => 'perfil'],
                    ],
                ],
            ],
        );

        $validResponse->assertOk()->assertHeader('content-type', 'application/pdf');
        $pdfText = (new \Smalot\PdfParser\Parser)->parseContent($validResponse->getContent())->getText();
        $this->assertStringContainsString('Excel avanzado', $pdfText);

        $invalidResponse = $this->post(
            route('estudiante.vacantes.cv-adaptado', $vacancy),
            [
                'consent_confirmed' => '1',
                'cv_fingerprint' => hash('sha256', $cvContents),
                'profile_only' => '0',
                'adapted_cv' => [
                    'profile' => [
                        'text' => 'Conocimientos de Excel avanzado.',
                        'evidence' => [['source' => 'perfil', 'quote' => $profileCitation]],
                    ],
                    'skills' => [
                        ['text' => 'Experto en Kubernetes', 'evidence' => $profileCitation, 'source' => 'perfil'],
                    ],
                ],
            ],
        );

        $invalidResponse->assertOk()->assertHeader('content-type', 'application/pdf');
        $invalidPdfText = (new \Smalot\PdfParser\Parser)->parseContent($invalidResponse->getContent())->getText();
        $this->assertStringContainsString('Experto en Kubernetes', $invalidPdfText);
        $this->assertStringContainsString('El estudiante confirmó como verdadero', $invalidPdfText);
    }

    public function test_compatibility_score_uses_verified_evidence_from_both_sources(): void
    {
        config(['services.gemini.key' => 'fake-test-key', 'services.gemini.model' => 'gemini-2.5-flash']);
        $payload = $this->geminiPayload();
        $payload['criteria'] = [
            [
                'id' => 'R1',
                'evidence' => 'Excel avanzado',
                'source' => 'perfil',
                'match' => 'direct',
                'explanation' => 'Habilidad registrada en el perfil.',
            ],
            [
                'id' => 'R2',
                'evidence' => 'PHP Laravel',
                'source' => 'cv',
                'match' => 'related',
                'explanation' => 'El CV aporta evidencia relacionada.',
            ],
        ];
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->wrapGemini($payload), 200)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $this->addSufficientProfile($student);
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('local')->put($student->cv_path, $this->pdfWithText('PHP Laravel'));

        $response = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.analizar-cv', $vacancy),
            $this->consentData(),
        );

        $response->assertOk()->assertSee('75.0%');
        $analysis = $response->viewData('analysis');
        $this->assertSame(['perfil', 'cv'], array_column($analysis['criteria'], 'source'));
        $this->assertSame(75.0, $analysis['compatibility_percentage']);
    }

    public function test_unpublished_vacancy_cannot_be_analyzed_or_used_for_adapted_cv_download(): void
    {
        Http::fake();
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $cvContents = $this->pdfWithText('PHP Laravel');
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('local')->put($student->cv_path, $cvContents);
        $vacancy->update(['estado' => 'cerrada']);

        $this->actingAs($studentUser)
            ->post(route('estudiante.vacantes.analizar-cv', $vacancy), $this->consentData())
            ->assertNotFound();

        $this->post(
            route('estudiante.vacantes.cv-adaptado', $vacancy),
            [
                'consent_confirmed' => '1',
                'cv_fingerprint' => hash('sha256', $cvContents),
                'profile_only' => '0',
                'adapted_cv' => [
                    'profile' => [
                        'text' => 'Conocimientos de PHP y Laravel.',
                        'evidence' => [['source' => 'cv', 'quote' => 'PHP Laravel']],
                    ],
                ],
            ],
        )->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_profile_block_is_capped_without_cutting_profile_records(): void
    {
        config(['services.gemini.key' => 'fake-test-key', 'services.gemini.model' => 'gemini-2.5-flash']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse(), 200)]);
        [$studentUser, $student, $vacancy] = $this->createContext();

        for ($index = 1; $index <= 10; $index++) {
            EstudianteExperiencia::create([
                'estudiante_id' => $student->id,
                'empresa' => 'Empresa '.$index,
                'cargo' => 'Cargo '.$index,
                'tipo' => 'empleo',
                'fecha_inicio' => sprintf('202%d-01-01', $index % 10),
                'actual' => false,
                'descripcion' => 'EXPERIENCIA-'.$index.' '.str_repeat('x', 850),
            ]);
        }

        $response = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.analizar-cv', $vacancy),
            $this->consentData(),
        );

        $response->assertOk();
        Http::assertSent(function (ClientRequest $request) use ($student): bool {
            $prompt = data_get($request->data(), 'contents.0.parts.0.text');
            if (! is_string($prompt)
                || ! preg_match('/\[PERFIL\]\n(.*?)\n\[\/PERFIL\]/s', $prompt, $matches)) {
                return false;
            }

            $profile = $matches[1];
            $includedLines = preg_split('/\R/u', $profile) ?: [];
            $originalLines = preg_split('/\R/u', $student->fresh()->perfilComoTexto()) ?: [];
            $completeExperienceLines = array_values(array_filter(
                $originalLines,
                fn (string $line): bool => str_starts_with($line, 'Experiencia:'),
            ));

            return mb_strlen($profile) <= 8_000
                && $includedLines !== []
                && count(array_intersect($includedLines, $completeExperienceLines)) === count($includedLines)
                && count($includedLines) < count($completeExperienceLines);
        });
    }

    private function createContext(): array
    {
        $career = Carrera::create(['nombre' => 'Informática']);
        $studentUser = User::factory()->create([
            'name' => 'Estudiante de Prueba',
            'email' => 'estudiante@example.test',
            'rol' => 'estudiante',
        ]);
        $student = Estudiante::create([
            'user_id' => $studentUser->id,
            'carrera_id' => $career->id,
            'matricula' => 'MATRICULA-TEST-01',
            'tipo' => 'estudiante',
        ]);
        $companyUser = User::factory()->create(['rol' => 'empresa']);
        $company = Empresa::create([
            'user_id' => $companyUser->id,
            'nombre_empresa' => 'Empresa de Prueba',
            'rnc' => 'RNC-'.Str::upper(Str::random(8)),
            'estado' => 'aprobada',
        ]);
        $vacancy = Vacante::create([
            'empresa_id' => $company->id,
            'titulo' => 'Desarrollador PHP',
            'descripcion' => 'Desarrollar aplicaciones web con PHP y Laravel.',
            'requisitos' => "Experiencia con PHP.\nManejo de bases de datos.",
            'tipo_empleo' => 'tiempo_completo',
            'modalidad' => 'remoto',
            'estado' => 'publicada',
        ]);
        $vacancy->carreras()->attach($career->id);

        return [$studentUser, $student, $vacancy];
    }

    private function addSufficientProfile(Estudiante $student): void
    {
        EstudianteFormacion::create([
            'estudiante_id' => $student->id,
            'institucion' => 'Instituto Técnico',
            'titulo' => 'Técnico en Informática',
            'nivel' => 'tecnico',
            'fecha_inicio' => '2023-01-01',
            'en_curso' => true,
        ]);

        foreach (['Excel avanzado', 'Análisis de datos', 'Comunicación'] as $name) {
            $skill = Habilidad::create(['nombre' => $name, 'tipo' => 'técnica']);
            $student->habilidades()->attach($skill->id, ['nivel' => 'intermedio']);
        }
    }

    private function geminiResponse(): array
    {
        return $this->wrapGemini($this->geminiPayload());
    }

    private function consentData(): array
    {
        return [
            'consent' => '1',
            'consent_version' => EmployabilityAnalysisController::CONSENT_VERSION,
        ];
    }

    private function geminiPayload(): array
    {
        return [
            'cv_information' => [
                'education' => [],
                'skills' => [['text' => 'PHP y Laravel', 'evidence' => 'PHP Laravel', 'source' => 'cv']],
                'experience' => [],
                'projects' => [],
                'certifications' => [],
                'technologies' => [['text' => 'PHP y Laravel', 'evidence' => 'PHP Laravel', 'source' => 'cv']],
                'languages' => [],
            ],
            'criteria' => [
                [
                    'id' => 'R1',
                    'evidence' => 'PHP Laravel',
                    'source' => 'cv',
                    'match' => 'direct',
                    'explanation' => 'El CV menciona explícitamente PHP y Laravel.',
                ],
                [
                    'id' => 'R2',
                    'evidence' => '',
                    'source' => 'none',
                    'match' => 'none',
                    'explanation' => 'No se encontró evidencia suficiente para este requisito.',
                ],
            ],
            'recommendations' => [
                ['kind' => 'highlight', 'text' => 'Destaca los conocimientos técnicos en una sección visible.', 'source' => 'cv', 'criterion_id' => '', 'evidence' => 'PHP Laravel'],
            ],
            'suggested_profile' => [
                'text' => 'Perfil con conocimientos documentados de PHP y Laravel.',
                'evidence' => [['source' => 'cv', 'quote' => 'PHP Laravel']],
            ],
            'adapted_cv' => [
                'profile' => ['text' => 'Conocimientos de PHP y Laravel.', 'evidence' => [['source' => 'cv', 'quote' => 'PHP Laravel']]],
                'education' => [],
                'skills' => [['text' => 'PHP y Laravel', 'evidence' => 'PHP Laravel', 'source' => 'cv']],
                'experience' => [],
                'projects' => [],
                'certifications' => [],
                'technologies' => [],
                'languages' => [],
            ],
        ];
    }

    private function wrapGemini(array $payload): array
    {
        return [
            'candidates' => [[
                'content' => [
                    'parts' => [[
                        'text' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    ]],
                ],
            ]],
        ];
    }

    private function pdfWithText(string $text): string
    {
        $pdf = new Dompdf;
        $pdf->loadHtml('<html><body>'.htmlspecialchars($text, ENT_QUOTES, 'UTF-8').'</body></html>');
        $pdf->render();

        return $pdf->output();
    }
}
