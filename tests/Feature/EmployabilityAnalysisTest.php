<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\User;
use App\Models\Vacante;
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
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf', 'cv_original_name' => 'cv.pdf']);
        Storage::disk('local')->put($student->cv_path, $pdf);

        $response = $this->actingAs($studentUser)->post(
            route('estudiante.vacantes.analizar-cv', $vacancy),
            ['consent' => '1'],
        );

        $response->assertOk()
            ->assertSee('50.0%')
            ->assertSee('Coincidencia directa')
            ->assertSee('No identificado en el CV')
            ->assertSee('Descargar CV adaptado en PDF');
        $analysis = $response->viewData('analysis');
        $this->assertSame('direct', $analysis['criteria'][0]['match']);
        $this->assertSame('none', $analysis['criteria'][1]['match']);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));

        Http::assertSent(function (ClientRequest $request) use ($studentUser, $student): bool {
            $body = json_encode($request->data(), JSON_UNESCAPED_UNICODE);

            return $request->hasHeader('x-goog-api-key', 'fake-test-key')
                && str_contains($request->url(), '/models/gemini-2.5-flash:generateContent')
                && ! str_contains($body, $studentUser->name)
                && ! str_contains($body, $studentUser->email)
                && ! str_contains($body, $student->matricula);
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
            ->post(route('estudiante.vacantes.analizar-cv', $vacancy))
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
            ['consent' => '1'],
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
                'consent' => '1',
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
                'adapted_cv' => [
                    'profile' => [
                        'text' => 'Conocimientos de PHP y Laravel.',
                        'evidence' => [$citation],
                    ],
                    'skills' => [
                        ['text' => 'PHP y Laravel', 'evidence' => $citation],
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
            ->post(route('estudiante.vacantes.analizar-cv', $vacancy), ['consent' => '1'])
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
            'cv_evidence' => 'Organización y control de registros y documentos',
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
            ['consent' => '1'],
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

    public function test_adapted_cv_excludes_items_without_valid_evidence(): void
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
            ['consent' => '1'],
        );
        $analysisResponse->assertOk();
        $analysis = $analysisResponse->viewData('analysis');
        $this->assertFalse($analysis['adapted_cv']['skills'][0]['evidence_valid']);
        $analysisResponse->assertDontSee('name="adapted_cv[skills][0][text]"', false);

        $downloadResponse = $this->post(
            route('estudiante.vacantes.cv-adaptado', $vacancy),
            [
                'consent_confirmed' => '1',
                'cv_fingerprint' => hash('sha256', $cvContents),
                'adapted_cv' => [
                    'profile' => [
                        'text' => 'Conocimientos de PHP y Laravel.',
                        'evidence' => ['PHP Laravel'],
                    ],
                    'skills' => [
                        ['text' => 'Experto en C++', 'evidence' => 'C++ experto'],
                    ],
                ],
            ],
        );

        $downloadResponse->assertOk()->assertHeader('content-type', 'application/pdf');
        $pdfText = (new \Smalot\PdfParser\Parser)->parseContent($downloadResponse->getContent())->getText();
        $this->assertStringNotContainsString('Experto en C++', $pdfText);
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
            ->post(route('estudiante.vacantes.analizar-cv', $vacancy), ['consent' => '1'])
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
            ->post(route('estudiante.vacantes.analizar-cv', $vacancy), ['consent' => '1'])
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
                'adapted_cv' => [
                    'profile' => [
                        'text' => 'Conocimientos de PHP y Laravel.',
                        'evidence' => [$citation],
                    ],
                    'skills' => [
                        ['text' => 'PHP y Laravel', 'evidence' => $citation],
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
            ->post(route('estudiante.vacantes.analizar-cv', $vacancy), ['consent' => '1'])
            ->assertForbidden();

        Http::assertNothingSent();
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

    private function geminiResponse(): array
    {
        return $this->wrapGemini($this->geminiPayload());
    }

    private function geminiPayload(): array
    {
        return [
            'cv_information' => [
                'education' => [],
                'skills' => [['text' => 'PHP y Laravel', 'evidence' => 'PHP Laravel']],
                'experience' => [],
                'projects' => [],
                'certifications' => [],
                'technologies' => [['text' => 'PHP y Laravel', 'evidence' => 'PHP Laravel']],
            ],
            'criteria' => [
                [
                    'id' => 'R1',
                    'cv_evidence' => 'PHP Laravel',
                    'match' => 'direct',
                    'explanation' => 'El CV menciona explícitamente PHP y Laravel.',
                ],
                [
                    'id' => 'R2',
                    'cv_evidence' => '',
                    'match' => 'none',
                    'explanation' => 'No se encontró evidencia suficiente para este requisito.',
                ],
            ],
            'recommendations' => [
                ['text' => 'Destaca los conocimientos técnicos en una sección visible.', 'source' => 'cv', 'evidence' => 'PHP Laravel'],
            ],
            'suggested_profile' => [
                'text' => 'Perfil con conocimientos documentados de PHP y Laravel.',
                'evidence' => ['PHP Laravel'],
            ],
            'adapted_cv' => [
                'profile' => ['text' => 'Conocimientos de PHP y Laravel.', 'evidence' => ['PHP Laravel']],
                'education' => [],
                'skills' => [['text' => 'PHP y Laravel', 'evidence' => 'PHP Laravel']],
                'experience' => [],
                'projects' => [],
                'certifications' => [],
                'technologies' => [],
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
