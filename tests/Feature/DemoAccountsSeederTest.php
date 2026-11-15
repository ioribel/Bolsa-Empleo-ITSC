<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\User;
use Database\Seeders\CarreraSeeder;
use Database\Seeders\DemoAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DemoAccountsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_three_approved_companies_and_complete_student_profiles(): void
    {
        Artisan::call('db:seed', ['--class' => CarreraSeeder::class]);
        Artisan::call('db:seed', ['--class' => DemoAccountsSeeder::class]);

        $this->assertSame(6, User::count());
        $this->assertSame(3, Empresa::where('estado', 'aprobada')->count());
        $this->assertSame(3, Estudiante::count());

        $this->assertDatabaseHas('users', [
            'email' => 'empresa.demo01@example.test',
            'rol' => 'empresa',
            'activo' => true,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'estudiante.demo01@example.test',
            'rol' => 'estudiante',
            'activo' => true,
        ]);

        $student = Estudiante::query()
            ->whereHas('user', fn ($query) => $query->where('email', 'estudiante.demo01@example.test'))
            ->firstOrFail();

        $this->assertSame(88, $student->porcentajeCompletitud());
        $this->assertSame(4, $student->habilidades()->count());
        $this->assertSame(1, $student->formaciones()->count());
        $this->assertSame(1, $student->experiencias()->count());
        $this->assertSame(1, $student->idiomas()->count());
        $this->assertSame(1, $student->certificaciones()->count());
    }
}
