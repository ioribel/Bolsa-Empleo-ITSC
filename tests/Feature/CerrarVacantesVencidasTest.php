<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CerrarVacantesVencidasTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_closes_only_published_vacancies_past_their_closing_date(): void
    {
        $this->travelTo(Carbon::parse('2026-10-27 12:00:00'));

        $empresa = Empresa::create([
            'user_id' => User::factory()->create(['rol' => 'empresa'])->id,
            'nombre_empresa' => 'Empresa de prueba',
            'rnc' => 'RNC-CIERRE-AUTOMATICO',
            'estado' => 'aprobada',
        ]);

        $vencida = $this->createVacante($empresa, 'publicada', '2026-10-26');
        $cierraHoy = $this->createVacante($empresa, 'publicada', '2026-10-27');
        $futura = $this->createVacante($empresa, 'publicada', '2026-10-28');
        $sinFecha = $this->createVacante($empresa, 'publicada', null);
        $yaCerrada = $this->createVacante($empresa, 'cerrada', '2026-10-26');

        $this->artisan('vacantes:cerrar-vencidas')
            ->expectsOutput('Se cerraron 1 vacantes vencidas.')
            ->assertExitCode(0);

        $this->assertSame('cerrada', $vencida->fresh()->estado);
        $this->assertSame('publicada', $cierraHoy->fresh()->estado);
        $this->assertSame('publicada', $futura->fresh()->estado);
        $this->assertSame('publicada', $sinFecha->fresh()->estado);
        $this->assertSame('cerrada', $yaCerrada->fresh()->estado);
    }

    private function createVacante(Empresa $empresa, string $estado, ?string $fechaCierre): Vacante
    {
        return $empresa->vacantes()->create([
            'titulo' => 'Vacante de prueba',
            'descripcion' => 'Descripción de prueba',
            'tipo_empleo' => 'tiempo_completo',
            'modalidad' => 'remoto',
            'fecha_cierre' => $fechaCierre,
            'estado' => $estado,
        ]);
    }
}
