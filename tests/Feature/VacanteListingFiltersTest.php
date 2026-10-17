<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VacanteListingFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_can_filter_its_vacancies_without_losing_create_action(): void
    {
        [$companyUser, $company] = $this->createCompany('Empresa propia');
        [, $otherCompany] = $this->createCompany('Otra empresa');
        $career = Carrera::create(['nombre' => 'Informática']);

        $matching = $this->createVacancy($company, 'Backend remoto', 'remoto', 'publicada');
        $matching->carreras()->attach($career->id);
        $otherStatus = $this->createVacancy($company, 'Backend borrador', 'remoto', 'borrador');
        $otherStatus->carreras()->attach($career->id);
        $otherOwner = $this->createVacancy($otherCompany, 'Backend de otra empresa', 'remoto', 'publicada');
        $otherOwner->carreras()->attach($career->id);

        $this->actingAs($companyUser)
            ->get(route('empresa.vacantes.index', [
                'buscar' => 'Backend',
                'estado' => 'publicada',
                'carrera' => $career->id,
                'modalidad' => 'remoto',
            ]))
            ->assertOk()
            ->assertSee('Backend remoto')
            ->assertSee('Nueva Vacante')
            ->assertDontSee('Backend borrador')
            ->assertDontSee('Backend de otra empresa');
    }

    public function test_student_can_filter_only_published_vacancies_for_their_career(): void
    {
        $career = Carrera::create(['nombre' => 'Informática']);
        $otherCareer = Carrera::create(['nombre' => 'Administración']);
        $studentUser = User::factory()->create(['rol' => 'estudiante']);
        Estudiante::create([
            'user_id' => $studentUser->id,
            'carrera_id' => $career->id,
            'matricula' => Str::upper(Str::random(10)),
            'tipo' => 'estudiante',
        ]);
        [, $company] = $this->createCompany('Empresa de prueba');

        $matching = $this->createVacancy($company, 'Backend remoto', 'remoto', 'publicada');
        $matching->carreras()->attach($career->id);
        $wrongModality = $this->createVacancy($company, 'Backend presencial', 'presencial', 'publicada');
        $wrongModality->carreras()->attach($career->id);
        $wrongStatus = $this->createVacancy($company, 'Backend cerrada', 'remoto', 'cerrada');
        $wrongStatus->carreras()->attach($career->id);
        $wrongCareer = $this->createVacancy($company, 'Backend otra carrera', 'remoto', 'publicada');
        $wrongCareer->carreras()->attach($otherCareer->id);

        $this->actingAs($studentUser)
            ->get(route('estudiante.vacantes.index', [
                'buscar' => 'Backend',
                'modalidad' => 'remoto',
                'tipo_empleo' => 'tiempo_completo',
            ]))
            ->assertOk()
            ->assertSee('Backend remoto')
            ->assertDontSee('Backend presencial')
            ->assertDontSee('Backend cerrada')
            ->assertDontSee('Backend otra carrera');
    }

    public function test_admin_can_filter_companies_users_and_careers(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        [$companyUser] = $this->createCompany('Mercado Central');
        Empresa::where('user_id', $companyUser->id)->update(['estado' => 'pendiente']);
        $this->createCompany('Mercado Norte');
        User::factory()->create([
            'name' => 'Usuario Estudiante Activo',
            'rol' => 'estudiante',
            'email_verified_at' => now(),
        ]);
        User::factory()->create([
            'name' => 'Usuario Estudiante Bloqueado',
            'rol' => 'estudiante',
            'email_verified_at' => null,
        ]);
        Carrera::create(['nombre' => 'Ingeniería de Software']);
        Carrera::create(['nombre' => 'Administración']);

        $this->actingAs($admin)
            ->get(route('admin.empresas.index', ['buscar' => 'Central', 'estado' => 'pendiente']))
            ->assertOk()
            ->assertSee('Mercado Central')
            ->assertDontSee('Mercado Norte');

        $this->get(route('admin.usuarios.index', ['buscar' => 'Usuario Estudiante', 'rol' => 'estudiante', 'estado' => 'activo']))
            ->assertOk()
            ->assertSee('Usuario Estudiante Activo')
            ->assertDontSee('Usuario Estudiante Bloqueado');

        $this->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertDontSee('Mercado Central')
            ->assertDontSee('Mercado Norte')
            ->assertDontSee('Usuario Empresa Activo');

        $this->get(route('admin.carreras.index', ['buscar' => 'Software']))
            ->assertOk()
            ->assertSee('Ingeniería de Software')
            ->assertDontSee('Administración')
            ->assertSee('Agregar carrera');
    }

    private function createCompany(string $name): array
    {
        $user = User::factory()->create(['rol' => 'empresa']);
        $company = Empresa::create([
            'user_id' => $user->id,
            'nombre_empresa' => $name,
            'rnc' => 'RNC-'.Str::upper(Str::random(8)),
            'estado' => 'aprobada',
        ]);

        return [$user, $company];
    }

    private function createVacancy(Empresa $company, string $title, string $modality, string $status): Vacante
    {
        return Vacante::create([
            'empresa_id' => $company->id,
            'titulo' => $title,
            'descripcion' => 'Descripción de '.$title,
            'tipo_empleo' => 'tiempo_completo',
            'modalidad' => $modality,
            'estado' => $status,
        ]);
    }
}