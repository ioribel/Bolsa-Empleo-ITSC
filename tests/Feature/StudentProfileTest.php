<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\EstudianteCertificacion;
use App\Models\EstudianteExperiencia;
use App\Models\EstudianteFormacion;
use App\Models\EstudianteIdioma;
use App\Models\Habilidad;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_create_update_and_delete_each_profile_section(): void
    {
        [$user, $student] = $this->createStudent();

        $this->actingAs($user)
            ->post(route('estudiante.perfil.formaciones.store'), $this->formacionData())
            ->assertSessionHasNoErrors();
        $formacion = $student->formaciones()->firstOrFail();
        $this->put(route('estudiante.perfil.formaciones.update', $formacion), $this->formacionData(['titulo' => 'Técnico actualizado']))
            ->assertSessionHasNoErrors();
        $this->assertSame('Técnico actualizado', $formacion->fresh()->titulo);
        $this->delete(route('estudiante.perfil.formaciones.destroy', $formacion))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('estudiante_formaciones', ['id' => $formacion->id]);

        $this->post(route('estudiante.perfil.experiencias.store'), $this->experienciaData())
            ->assertSessionHasNoErrors();
        $experiencia = $student->experiencias()->firstOrFail();
        $this->put(route('estudiante.perfil.experiencias.update', $experiencia), $this->experienciaData(['cargo' => 'Analista actualizado']))
            ->assertSessionHasNoErrors();
        $this->assertSame('Analista actualizado', $experiencia->fresh()->cargo);
        $this->delete(route('estudiante.perfil.experiencias.destroy', $experiencia))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('estudiante_experiencias', ['id' => $experiencia->id]);

        $this->post(route('estudiante.perfil.habilidades.store'), [
            'nombre' => '  PHP  ',
            'tipo' => 'técnica',
            'nivel' => 'intermedio',
        ])->assertSessionHasNoErrors();
        $skill = Habilidad::where('nombre', 'php')->firstOrFail();
        $this->put(route('estudiante.perfil.habilidades.update', $skill), [
            'nombre' => 'Laravel',
            'tipo' => 'técnica',
            'nivel' => 'avanzado',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('estudiante_habilidad', [
            'estudiante_id' => $student->id,
            'habilidad_id' => Habilidad::where('nombre', 'laravel')->value('id'),
            'nivel' => 'avanzado',
        ]);
        $this->delete(route('estudiante.perfil.habilidades.destroy', Habilidad::where('nombre', 'laravel')->firstOrFail()))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('estudiante_habilidad', ['estudiante_id' => $student->id]);

        $this->post(route('estudiante.perfil.idiomas.store'), ['idioma' => 'Inglés', 'nivel' => 'avanzado'])
            ->assertSessionHasNoErrors();
        $idioma = $student->idiomas()->firstOrFail();
        $this->put(route('estudiante.perfil.idiomas.update', $idioma), ['idioma' => 'francés', 'nivel' => 'intermedio'])
            ->assertSessionHasNoErrors();
        $this->assertSame('francés', $idioma->fresh()->idioma);
        $this->delete(route('estudiante.perfil.idiomas.destroy', $idioma))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('estudiante_idiomas', ['id' => $idioma->id]);

        $this->post(route('estudiante.perfil.certificaciones.store'), $this->certificacionData())
            ->assertSessionHasNoErrors();
        $certificacion = $student->certificaciones()->firstOrFail();
        $this->put(route('estudiante.perfil.certificaciones.update', $certificacion), $this->certificacionData(['nombre' => 'Certificación actualizada']))
            ->assertSessionHasNoErrors();
        $this->assertSame('Certificación actualizada', $certificacion->fresh()->nombre);
        $this->delete(route('estudiante.perfil.certificaciones.destroy', $certificacion))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('estudiante_certificaciones', ['id' => $certificacion->id]);
    }

    public function test_student_cannot_update_or_delete_another_students_records(): void
    {
        [$owner] = $this->createStudent();
        [$otherUser, $otherStudent] = $this->createStudent();
        $formation = $otherStudent->formaciones()->create($this->formacionData());
        $experience = $otherStudent->experiencias()->create($this->experienciaData());
        $language = $otherStudent->idiomas()->create(['idioma' => 'inglés', 'nivel' => 'avanzado']);
        $certificate = $otherStudent->certificaciones()->create($this->certificacionData());
        $skill = Habilidad::create(['nombre' => 'php', 'tipo' => 'técnica']);
        $otherStudent->habilidades()->attach($skill, ['nivel' => 'intermedio']);

        $this->actingAs($owner)
            ->put(route('estudiante.perfil.formaciones.update', $formation), $this->formacionData())
            ->assertNotFound();
        $this->delete(route('estudiante.perfil.formaciones.destroy', $formation))->assertNotFound();
        $this->put(route('estudiante.perfil.experiencias.update', $experience), $this->experienciaData())->assertNotFound();
        $this->delete(route('estudiante.perfil.experiencias.destroy', $experience))->assertNotFound();
        $this->put(route('estudiante.perfil.idiomas.update', $language), ['idioma' => 'francés', 'nivel' => 'nativo'])->assertNotFound();
        $this->delete(route('estudiante.perfil.idiomas.destroy', $language))->assertNotFound();
        $this->put(route('estudiante.perfil.certificaciones.update', $certificate), $this->certificacionData())->assertNotFound();
        $this->delete(route('estudiante.perfil.certificaciones.destroy', $certificate))->assertNotFound();
        $this->put(route('estudiante.perfil.habilidades.update', $skill), ['nombre' => 'python', 'tipo' => 'técnica', 'nivel' => 'avanzado'])->assertNotFound();
        $this->delete(route('estudiante.perfil.habilidades.destroy', $skill))->assertNotFound();

        $this->assertSame('inglés', $language->fresh()->idioma);
        $this->assertSame('php', $skill->fresh()->nombre);
        $this->assertSame($otherUser->id, $otherStudent->fresh()->user_id);
    }

    public function test_only_students_with_profiles_can_access_profile_routes(): void
    {
        [$student] = $this->createStudent();
        $admin = User::factory()->create(['rol' => 'admin']);
        $company = User::factory()->create(['rol' => 'empresa']);
        $studentWithoutProfile = User::factory()->create(['rol' => 'estudiante']);

        $this->actingAs($admin)->get(route('estudiante.perfil.edit'))->assertForbidden();
        $this->actingAs($company)->get(route('estudiante.perfil.edit'))->assertForbidden();
        $this->actingAs($admin)->post(route('estudiante.perfil.formaciones.store'), $this->formacionData())->assertForbidden();
        $this->actingAs($company)->post(route('estudiante.perfil.formaciones.store'), $this->formacionData())->assertForbidden();
        $this->actingAs($studentWithoutProfile)->get(route('estudiante.perfil.edit'))->assertForbidden();
        $this->actingAs($student)->post(route('estudiante.perfil.formaciones.store'), $this->formacionData())->assertSessionHasNoErrors();
    }

    public function test_profile_page_renders_every_section_and_has_no_nested_forms(): void
    {
        [$user] = $this->createStudent();

        $response = $this->actingAs($user)->get(route('estudiante.perfil.edit'))
            ->assertOk()
            ->assertSee('Completitud del perfil')
            ->assertSee('Datos básicos y enlaces')
            ->assertSee('Formación académica')
            ->assertSee('Experiencia laboral')
            ->assertSee('Habilidades')
            ->assertSee('Idiomas')
            ->assertSee('Certificaciones');

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
    }

    public function test_profile_validation_rejects_incoherent_dates_invalid_domains_duplicate_languages_and_section_limits(): void
    {
        [$user, $student] = $this->createStudent();
        $this->actingAs($user);

        $this->post(route('estudiante.perfil.formaciones.store'), $this->formacionData([
            'fecha_inicio' => '2025-01-01',
            'fecha_fin' => '2024-12-31',
        ]))->assertSessionHasErrors('fecha_fin');
        $this->post(route('estudiante.perfil.formaciones.store'), $this->formacionData([
            'en_curso' => '1',
            'fecha_fin' => '2026-01-01',
        ]))->assertSessionHasErrors('fecha_fin');
        $this->post(route('estudiante.perfil.experiencias.store'), $this->experienciaData([
            'actual' => '1',
            'fecha_fin' => '2026-01-01',
        ]))->assertSessionHasErrors('fecha_fin');

        $this->post(route('estudiante.perfil.idiomas.store'), ['idioma' => 'Inglés', 'nivel' => 'intermedio'])
            ->assertSessionHasNoErrors();
        $this->post(route('estudiante.perfil.idiomas.store'), ['idioma' => '  INGLÉS ', 'nivel' => 'avanzado'])
            ->assertSessionHasErrors('idioma');

        $this->post(route('estudiante.perfil.update'), [
            'linkedin_url' => 'https://linkedin.example.com/in/persona',
            'github_url' => 'https://github.com/usuario',
            'portafolio_url' => 'https://portfolio.example',
        ])->assertSessionHasErrors('linkedin_url');

        EstudianteFormacion::factory()->count(10)->create(['estudiante_id' => $student->id]);
        $this->post(route('estudiante.perfil.formaciones.store'), $this->formacionData())->assertSessionHasErrors('formaciones');
        EstudianteExperiencia::factory()->count(15)->create(['estudiante_id' => $student->id]);
        $this->post(route('estudiante.perfil.experiencias.store'), $this->experienciaData())->assertSessionHasErrors('experiencias');
        EstudianteIdioma::factory()->count(7)->create(['estudiante_id' => $student->id]);
        $this->post(route('estudiante.perfil.idiomas.store'), ['idioma' => 'esperanto', 'nivel' => 'básico'])->assertSessionHasErrors('idiomas');
        EstudianteCertificacion::factory()->count(15)->create(['estudiante_id' => $student->id]);
        $this->post(route('estudiante.perfil.certificaciones.store'), $this->certificacionData())->assertSessionHasErrors('certificaciones');

        $skills = Habilidad::factory()->count(30)->create();
        foreach ($skills as $skill) {
            $student->habilidades()->attach($skill->id, ['nivel' => 'básico']);
        }
        $this->post(route('estudiante.perfil.habilidades.store'), [
            'nombre' => 'Nueva habilidad',
            'tipo' => 'técnica',
            'nivel' => 'básico',
        ])->assertSessionHasErrors('habilidades');
    }

    public function test_company_sees_structured_profile_only_for_applicants_to_its_own_vacancy(): void
    {
        [$companyUser, $vacancy] = $this->createCompanyWithVacancy('Empresa primera');
        [$otherCompanyUser, $otherVacancy] = $this->createCompanyWithVacancy('Empresa segunda');
        [, $candidate] = $this->createStudent(['titular' => 'CANDIDATO VISIBLE']);
        [, $otherCompanyCandidate] = $this->createStudent(['titular' => 'CANDIDATO OTRA EMPRESA']);
        [, $neverAppliedCandidate] = $this->createStudent(['titular' => 'CANDIDATO SIN POSTULACION']);

        $this->addApplication($vacancy, $candidate);
        $this->addApplication($otherVacancy, $otherCompanyCandidate);

        $candidate->formaciones()->create($this->formacionData(['titulo' => 'FORMACION VISIBLE']));
        $candidate->experiencias()->create($this->experienciaData(['cargo' => 'EXPERIENCIA VISIBLE']));
        $candidate->idiomas()->create(['idioma' => 'inglés', 'nivel' => 'avanzado']);
        $candidate->certificaciones()->create($this->certificacionData(['nombre' => 'CERTIFICACION VISIBLE']));
        $skill = Habilidad::create(['nombre' => 'habilidad visible', 'tipo' => 'técnica']);
        $candidate->habilidades()->attach($skill, ['nivel' => 'avanzado']);

        $this->actingAs($companyUser)
            ->get(route('empresa.vacantes.postulaciones.index', $vacancy))
            ->assertOk()
            ->assertSee('CANDIDATO VISIBLE')
            ->assertSee('FORMACION VISIBLE')
            ->assertSee('EXPERIENCIA VISIBLE')
            ->assertSee('habilidad visible')
            ->assertSee('Inglés')
            ->assertSee('CERTIFICACION VISIBLE')
            ->assertDontSee('CANDIDATO OTRA EMPRESA')
            ->assertDontSee('CANDIDATO SIN POSTULACION');

        $this->assertNotNull($neverAppliedCandidate->id);
    }

    public function test_profile_completion_percentage_uses_eight_documented_criteria(): void
    {
        [, $student] = $this->createStudent();
        $this->assertSame(0, $student->porcentajeCompletitud());

        $student->update(['titular' => 'Estudiante de Software']);
        $this->assertSame(13, $student->fresh()->porcentajeCompletitud());
        $student->update([
            'resumen' => 'Resumen',
            'cv_path' => 'cvs/perfil.pdf',
            'linkedin_url' => 'https://linkedin.com/in/perfil',
        ]);
        $student->formaciones()->create($this->formacionData());
        $student->experiencias()->create($this->experienciaData());
        $student->idiomas()->create(['idioma' => 'inglés', 'nivel' => 'intermedio']);
        foreach (['php', 'sql', 'git'] as $name) {
            $skill = Habilidad::create(['nombre' => $name, 'tipo' => 'técnica']);
            $student->habilidades()->attach($skill, ['nivel' => 'intermedio']);
        }

        $this->assertSame(100, $student->fresh()->porcentajeCompletitud());
        $this->assertSame([], $student->fresh()->perfilPendienteCompletar());
    }

    public function test_profile_text_excludes_contact_information_identifiers_and_urls(): void
    {
        [$user, $student] = $this->createStudent();
        $user->update(['name' => 'NOMBRE PRIVADO']);
        $student->update([
            'telefono' => '8095551234',
            'matricula' => 'MAT-PRIVADA-77',
            'titular' => 'Desarrollador https://private.example',
            'resumen' => "NOMBRE PRIVADO. Correo {$user->email}, teléfono 8095551234, matrícula MAT-PRIVADA-77. GitHub https://github.com/private",
            'linkedin_url' => 'https://linkedin.com/in/private',
        ]);
        $student->formaciones()->create($this->formacionData([
            'descripcion' => 'Contacto privado@example.com y matrícula MAT-PRIVADA-77',
        ]));

        $text = $student->fresh()->perfilComoTexto();

        $this->assertStringNotContainsString($user->email, $text);
        $this->assertStringNotContainsString('8095551234', $text);
        $this->assertStringNotContainsString('MAT-PRIVADA-77', $text);
        $this->assertStringNotContainsString('https://', $text);
        $this->assertStringNotContainsString('NOMBRE PRIVADO', $text);
        $this->assertStringContainsString('Desarrollador', $text);
    }

    public function test_skills_are_normalized_and_not_duplicated_for_a_student(): void
    {
        [$user, $student] = $this->createStudent();
        $this->actingAs($user);

        $this->post(route('estudiante.perfil.habilidades.store'), [
            'nombre' => '  PHP   ',
            'tipo' => 'técnica',
            'nivel' => 'básico',
        ])->assertSessionHasNoErrors();
        $this->post(route('estudiante.perfil.habilidades.store'), [
            'nombre' => 'php',
            'tipo' => 'técnica',
            'nivel' => 'avanzado',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('habilidades', 1);
        $this->assertDatabaseCount('estudiante_habilidad', 1);
        $this->assertDatabaseHas('estudiante_habilidad', [
            'estudiante_id' => $student->id,
            'nivel' => 'avanzado',
        ]);
    }

    private function createStudent(array $attributes = []): array
    {
        $student = Estudiante::factory()->create($attributes);

        return [$student->user, $student];
    }

    private function createCompanyWithVacancy(string $name): array
    {
        $user = User::factory()->create(['rol' => 'empresa']);
        $company = Empresa::create([
            'user_id' => $user->id,
            'nombre_empresa' => $name,
            'rnc' => 'RNC-'.Str::upper(Str::random(10)),
            'estado' => 'aprobada',
        ]);
        $vacancy = Vacante::create([
            'empresa_id' => $company->id,
            'titulo' => 'Vacante '.$name,
            'descripcion' => 'Descripción',
            'tipo_empleo' => 'tiempo_completo',
            'modalidad' => 'remoto',
            'estado' => 'publicada',
        ]);

        return [$user, $vacancy];
    }

    private function addApplication(Vacante $vacancy, Estudiante $student): Postulacion
    {
        return $vacancy->postulaciones()->create([
            'estudiante_id' => $student->id,
            'estado' => 'recibida',
        ]);
    }

    private function formacionData(array $overrides = []): array
    {
        return array_merge([
            'institucion' => 'Instituto Técnico',
            'titulo' => 'Técnico en Desarrollo',
            'nivel' => 'tecnico',
            'campo_estudio' => 'Software',
            'fecha_inicio' => '2022-01-01',
            'fecha_fin' => '2024-01-01',
            'en_curso' => '0',
            'descripcion' => 'Formación profesional',
        ], $overrides);
    }

    private function experienciaData(array $overrides = []): array
    {
        return array_merge([
            'empresa' => 'Empresa de prueba',
            'cargo' => 'Desarrollador junior',
            'tipo' => 'pasantia',
            'ubicacion' => 'Santo Domingo',
            'fecha_inicio' => '2023-01-01',
            'fecha_fin' => '2023-12-01',
            'actual' => '0',
            'descripcion' => 'Apoyo al equipo',
        ], $overrides);
    }

    private function certificacionData(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Certificación PHP',
            'entidad_emisora' => 'ITSC',
            'fecha_emision' => '2024-01-01',
            'fecha_vencimiento' => null,
            'url_credencial' => 'https://example.com/credencial',
        ], $overrides);
    }
}
