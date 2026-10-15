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
        Storage::disk('local')->assertExists($cvPath);

        $this->get(route('estudiante.perfil.cv'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
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