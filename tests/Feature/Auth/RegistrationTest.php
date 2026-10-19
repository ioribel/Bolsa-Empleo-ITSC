<?php

namespace Tests\Feature\Auth;

use App\Models\Carrera;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200)
            ->assertSee('Nombre')
            ->assertSee('Correo electrónico')
            ->assertSee('Confirmar contraseña')
            ->assertSee('¿Ya tienes una cuenta?')
            ->assertSee('Registrarme')
            ->assertDontSee('Already registered?');

        $this->get('/register/empresa')
            ->assertOk()
            ->assertSee('Nombre')
            ->assertSee('Correo electrónico')
            ->assertSee('Registrarme');
    }

    public function test_new_users_can_register(): void
    {
        $career = Carrera::create(['nombre' => 'Informática']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'matricula' => 'MAT-TEST-001',
            'carrera_id' => $career->id,
            'tipo' => 'estudiante',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_new_companies_wait_for_approval_before_signing_in(): void
    {
        $response = $this->post('/register/empresa', [
            'name' => 'Empresa User',
            'email' => 'empresa@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'nombre_empresa' => 'Empresa Pendiente',
            'rnc' => 'RNC-12345678',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Tu solicitud para registrar la empresa fue recibida. Podrás iniciar sesión cuando el perfil sea aprobado; te avisaremos por correo.');
        $this->assertDatabaseHas('empresas', [
            'nombre_empresa' => 'Empresa Pendiente',
            'estado' => 'pendiente',
        ]);
    }
}
