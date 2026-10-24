<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentCvTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_upload_and_open_profile_cv_without_public_storage_link(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [$studentUser, $estudiante] = $this->createStudent();

        $this->actingAs($studentUser)
            ->post(route('estudiante.perfil.update'), [
                'cv' => UploadedFile::fake()->create('curriculum.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $cvPath = $estudiante->refresh()->cv_path;
        $this->assertSame('curriculum.pdf', $estudiante->cv_original_name);
        Storage::disk('local')->assertExists($cvPath);

        $this->get(route('estudiante.perfil.cv'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_student_can_replace_profile_cv_and_remove_previous_file(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [$studentUser, $estudiante] = $this->createStudent();
        $oldCvPath = 'cvs/perfiles/cv-anterior.pdf';
        Storage::disk('local')->put($oldCvPath, '%PDF-1.4 old');
        $estudiante->update([
            'cv_path' => $oldCvPath,
            'cv_original_name' => 'cv-anterior.pdf',
        ]);

        $this->actingAs($studentUser)
            ->post(route('estudiante.perfil.update'), [
                'cv' => UploadedFile::fake()->create('cv-actualizado.pdf', 120, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Perfil actualizado.');

        $estudiante->refresh();
        $this->assertSame('cv-actualizado.pdf', $estudiante->cv_original_name);
        $this->assertNotSame($oldCvPath, $estudiante->cv_path);
        Storage::disk('local')->assertExists($estudiante->cv_path);
        Storage::disk('local')->assertMissing($oldCvPath);
    }

    public function test_student_can_delete_their_private_profile_cv(): void
    {
        Storage::fake('local');
        [$studentUser, $estudiante] = $this->createStudent();
        $cvPath = 'cvs/perfiles/curriculum.pdf';
        Storage::disk('local')->put($cvPath, '%PDF-1.4 fake');
        $estudiante->update([
            'cv_path' => $cvPath,
            'cv_original_name' => 'curriculum.pdf',
        ]);

        $this->actingAs($studentUser)
            ->delete(route('estudiante.perfil.cv.destroy'))
            ->assertSessionHas('status', 'Currículum eliminado.');

        Storage::disk('local')->assertMissing($cvPath);
        $this->assertNull($estudiante->refresh()->cv_path);
        $this->assertNull($estudiante->cv_original_name);
    }

    public function test_student_profile_page_renders_upload_and_delete_as_separate_forms(): void
    {
        Storage::fake('local');
        [$studentUser, $estudiante] = $this->createStudent();
        $cvPath = 'cvs/perfiles/curriculum.pdf';
        Storage::disk('local')->put($cvPath, '%PDF-1.4 fake');
        $estudiante->update(['cv_path' => $cvPath, 'cv_original_name' => 'curriculum.pdf']);

        $response = $this->actingAs($studentUser)->get(route('estudiante.perfil.edit'));
        $response->assertOk();

        $document = new \DOMDocument;
        $previousErrorHandling = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorHandling);
        $this->assertTrue($loaded);
        $xpath = new \DOMXPath($document);

        foreach ($xpath->query('//form') as $form) {
            $this->assertSame(0, $xpath->query('.//form', $form)->length);
        }

        $uploadForms = $xpath->query('//form[.//input[@name="cv"]]');
        $deleteForms = $xpath->query('//form[.//input[@name="_method" and @value="DELETE"]]');

        $this->assertSame(1, $uploadForms->length);
        $this->assertSame(1, $deleteForms->length);
        $this->assertFalse($uploadForms->item(0)->isSameNode($deleteForms->item(0)));
    }

    public function test_uploaded_application_cv_is_visible_only_to_the_vacancy_owner(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [$studentUser, $estudiante, $vacante, $empresaUser] = $this->createApplicationContext();

        $this->actingAs($studentUser)
            ->post(route('estudiante.vacantes.postular', $vacante), [
                'cv' => UploadedFile::fake()->create('curriculum.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $postulacion = Postulacion::where('estudiante_id', $estudiante->id)->firstOrFail();
        Storage::disk('local')->assertExists($postulacion->cv_path);

        $this->actingAs($empresaUser)
            ->get(route('empresa.postulaciones.cv', $postulacion))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $otherCompanyUser = User::factory()->create(['rol' => 'empresa']);
        Empresa::create([
            'user_id' => $otherCompanyUser->id,
            'nombre_empresa' => 'Otra empresa',
            'rnc' => 'RNC-'.Str::upper(Str::random(8)),
        ]);

        $this->actingAs($otherCompanyUser)
            ->get(route('empresa.postulaciones.cv', $postulacion))
            ->assertNotFound();
    }

    public function test_profile_cv_is_copied_to_the_application_when_no_new_file_is_selected(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [$studentUser, $estudiante, $vacante, $empresaUser] = $this->createApplicationContext();
        $profileCvPath = 'cvs/perfil-antiguo.pdf';
        Storage::disk('public')->put($profileCvPath, '%PDF-1.4 profile');
        $estudiante->update(['cv_path' => $profileCvPath]);

        $this->actingAs($studentUser)
            ->post(route('estudiante.vacantes.postular', $vacante))
            ->assertSessionHasNoErrors();

        $postulacion = Postulacion::where('estudiante_id', $estudiante->id)->firstOrFail();
        Storage::disk('local')->assertExists($postulacion->cv_path);

        $this->actingAs($empresaUser)
            ->get(route('empresa.postulaciones.cv', $postulacion))
            ->assertOk();
    }

    public function test_student_can_filter_applications_by_vacancy_and_status(): void
    {
        Storage::fake('local');
        [$studentUser, $student, $vacancy] = $this->createApplicationContext();
        $vacancy->postulaciones()->create([
            'estudiante_id' => $student->id,
            'estado' => 'recibida',
        ]);

        $secondVacancy = Vacante::create([
            'empresa_id' => $vacancy->empresa_id,
            'titulo' => 'Asistente Administrativo',
            'descripcion' => 'Apoyo administrativo',
            'tipo_empleo' => 'medio_tiempo',
            'modalidad' => 'remoto',
            'estado' => 'publicada',
        ]);
        $secondVacancy->carreras()->attach($student->carrera_id);
        $secondVacancy->postulaciones()->create([
            'estudiante_id' => $student->id,
            'estado' => 'en_revision',
        ]);

        $this->actingAs($studentUser)
            ->get(route('estudiante.postulaciones.index', ['buscar' => 'Desarrollador']))
            ->assertOk()
            ->assertSee('Desarrollador de prueba')
            ->assertDontSee('Asistente Administrativo')
            ->assertSee('1 postulación');

        $this->get(route('estudiante.postulaciones.index', ['estado' => 'en_revision']))
            ->assertOk()
            ->assertSee('Asistente Administrativo')
            ->assertDontSee('Desarrollador de prueba')
            ->assertSee('1 postulación');
    }

    public function test_student_must_attach_a_cv_when_the_profile_has_none(): void
    {
        [$studentUser, , $vacante] = $this->createApplicationContext();

        $this->actingAs($studentUser)
            ->post(route('estudiante.vacantes.postular', $vacante))
            ->assertSessionHasErrors('cv');

        $this->assertDatabaseCount('postulaciones', 0);
    }

    private function createStudent(): array
    {
        $carrera = Carrera::create(['nombre' => 'Informática']);
        $studentUser = User::factory()->create(['rol' => 'estudiante']);
        $estudiante = Estudiante::create([
            'user_id' => $studentUser->id,
            'carrera_id' => $carrera->id,
            'matricula' => Str::upper(Str::random(10)),
            'tipo' => 'estudiante',
        ]);

        return [$studentUser, $estudiante, $carrera];
    }

    private function createApplicationContext(): array
    {
        [$studentUser, $estudiante, $carrera] = $this->createStudent();
        $companyUser = User::factory()->create(['rol' => 'empresa']);
        $empresa = Empresa::create([
            'user_id' => $companyUser->id,
            'nombre_empresa' => 'Empresa de prueba',
            'rnc' => 'RNC-'.Str::upper(Str::random(8)),
            'estado' => 'aprobada',
        ]);
        $vacante = Vacante::create([
            'empresa_id' => $empresa->id,
            'titulo' => 'Desarrollador de prueba',
            'descripcion' => 'Vacante de prueba',
            'tipo_empleo' => 'tiempo_completo',
            'modalidad' => 'presencial',
            'estado' => 'publicada',
        ]);
        $vacante->carreras()->attach($carrera->id);

        return [$studentUser, $estudiante, $vacante, $companyUser];
    }
}
