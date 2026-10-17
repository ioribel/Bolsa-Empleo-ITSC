<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Vacante;
use App\Notifications\EmpresaAprobadaNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminVacantesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_and_filter_published_vacancies(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $companyUser = User::factory()->create(['rol' => 'empresa']);
        $empresa = Empresa::create([
            'user_id' => $companyUser->id,
            'nombre_empresa' => 'Tecnología ITSC',
            'rnc' => 'RNC-'.Str::upper(Str::random(8)),
        ]);
        $carrera = Carrera::create(['nombre' => 'Informática']);

        $published = Vacante::create([
            'empresa_id' => $empresa->id,
            'titulo' => 'Desarrollador Backend',
            'descripcion' => 'Construcción de servicios web',
            'requisitos' => 'Experiencia con PHP',
            'tipo_empleo' => 'tiempo_completo',
            'modalidad' => 'remoto',
            'estado' => 'publicada',
        ]);
        $published->carreras()->attach($carrera->id);

        Vacante::create([
            'empresa_id' => $empresa->id,
            'titulo' => 'Diseñador presencial',
            'descripcion' => 'Diseño de interfaces',
            'tipo_empleo' => 'medio_tiempo',
            'modalidad' => 'presencial',
            'estado' => 'publicada',
        ]);
        Vacante::create([
            'empresa_id' => $empresa->id,
            'titulo' => 'Backend cerrado',
            'descripcion' => 'Vacante ya cerrada',
            'tipo_empleo' => 'tiempo_completo',
            'modalidad' => 'remoto',
            'estado' => 'cerrada',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.vacantes.index', [
                'buscar' => 'Backend',
                'carrera' => $carrera->id,
                'modalidad' => 'remoto',
            ]))
            ->assertOk()
            ->assertSee('Desarrollador Backend')
            ->assertSee('Experiencia con PHP')
            ->assertDontSee('Diseñador presencial')
            ->assertDontSee('Backend cerrado');
    }

    public function test_non_admin_cannot_view_admin_vacancies(): void
    {
        $student = User::factory()->create(['rol' => 'estudiante']);

        $this->actingAs($student)
            ->get(route('admin.vacantes.index'))
            ->assertForbidden();
    }

    public function test_approving_company_sends_email_notification_once(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['rol' => 'admin']);
        $companyUser = User::factory()->unverified()->create(['rol' => 'empresa']);
        $empresa = Empresa::create([
            'user_id' => $companyUser->id,
            'nombre_empresa' => 'Empresa Pendiente',
            'rnc' => 'RNC-APROBAR',
            'estado' => 'pendiente',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.empresas.index'))
            ->patch(route('admin.empresas.aprobar', $empresa))
            ->assertRedirect(route('admin.empresas.index'));

        $this->assertDatabaseHas('empresas', [
            'id' => $empresa->id,
            'estado' => 'aprobada',
        ]);
        $this->assertNotNull($companyUser->fresh()->email_verified_at);
        Notification::assertSentTo($companyUser, EmpresaAprobadaNotification::class);

        $this->patch(route('admin.empresas.aprobar', $empresa));

        Notification::assertSentToTimes($companyUser, EmpresaAprobadaNotification::class, 1);
    }

    public function test_company_accounts_cannot_be_activated_or_blocked_from_users(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $companyUser = User::factory()->unverified()->create(['rol' => 'empresa']);

        $this->actingAs($admin)
            ->patch(route('admin.usuarios.activar', $companyUser))
            ->assertNotFound();

        $this->patch(route('admin.usuarios.bloquear', $companyUser))
            ->assertNotFound();

        $this->assertNull($companyUser->fresh()->email_verified_at);
    }
}