<?php

namespace Tests\Feature\Auth;

use App\Models\Carrera;
use App\Models\User;
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
            ->assertSee('Mostrar contraseña')
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
            'password' => 'new-strong-password',
            'password_confirmation' => 'new-strong-password',
            'matricula' => 'MAT-TEST-001',
            'carrera_id' => $career->id,
            'tipo' => 'estudiante',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'activo' => true,
        ]);
    }

    public function test_new_companies_wait_for_approval_before_signing_in(): void
    {
        $response = $this->post('/register/empresa', [
            'name' => 'Empresa User',
            'email' => 'empresa@example.com',
            'password' => 'new-strong-password',
            'password_confirmation' => 'new-strong-password',
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

    public function test_registration_rejects_email_addresses_pending_confirmation_on_another_account(): void
    {
        $career = Carrera::create(['nombre' => 'Informática']);
        User::factory()->create(['pending_email' => 'reserved@example.com']);

        $this->from('/register')
            ->post('/register', [
                'name' => 'Test Student',
                'email' => 'reserved@example.com',
                'password' => 'new-strong-password',
                'password_confirmation' => 'new-strong-password',
                'matricula' => 'MAT-RESERVED-001',
                'carrera_id' => $career->id,
                'tipo' => 'estudiante',
            ])
            ->assertSessionHasErrors('email');

        $this->from('/register/empresa')
            ->post('/register/empresa', [
                'name' => 'Test Company',
                'email' => 'reserved@example.com',
                'password' => 'new-strong-password',
                'password_confirmation' => 'new-strong-password',
                'nombre_empresa' => 'Test Company',
                'rnc' => 'RNC-RESERVED-001',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_requires_a_password_of_at_least_twelve_characters(): void
    {
        $career = Carrera::create(['nombre' => 'Informática']);

        $this->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'short-password@example.com',
                'password' => 'short-pass',
                'password_confirmation' => 'short-pass',
                'matricula' => 'MAT-SHORT-001',
                'carrera_id' => $career->id,
                'tipo' => 'estudiante',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'short-password@example.com']);
    }

    public function test_registration_requests_are_limited_per_ip(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->from('/register')
                ->post('/register', [])
                ->assertSessionHasErrors();
        }

        $this->post('/register', [])->assertTooManyRequests();
    }
}
