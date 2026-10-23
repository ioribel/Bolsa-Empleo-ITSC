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
            ->assertSee('No identificado en el CV')
            ->assertSee('Descargar CV adaptado en PDF');
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
            Cache::get('cv-analysis:'.$student->user_id.':'.$analysis['cv_source']),
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

    public function test_invalid_ai_evidence_is_rejected_instead_of_presented_as_a_result(): void
    {
        $payload = $this->geminiPayload();
        $payload['cv_information']['skills'][0]['evidence'] = 'C++ experto';
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->wrapGemini($payload), 200)]);
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createContext();
        $student->update(['cv_path' => 'cvs/perfiles/cv.pdf']);
        Storage::disk('local')->put($student->cv_path, $this->pdfWithText('PHP Laravel desarrollo de software'));

        $this->actingAs($studentUser)
            ->from(route('estudiante.vacantes.show', $vacancy))
            ->post(route('estudiante.vacantes.analizar-cv', $vacancy), ['consent' => '1'])
            ->assertRedirect(route('estudiante.vacantes.show', $vacancy))
            ->assertSessionHas('error', 'Gemini devolvió una afirmación que no se pudo verificar en el documento.');
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
                ['id' => 'R1', 'cv_evidence' => 'PHP Laravel'],
                ['id' => 'R2', 'cv_evidence' => 'PHP Laravel'],
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
