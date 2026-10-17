<?php

namespace Tests\Feature\Auth;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_pending_companies_cannot_authenticate(): void
    {
        $user = User::factory()->create(['rol' => 'empresa']);
        Empresa::create([
            'user_id' => $user->id,
            'nombre_empresa' => 'Empresa Pendiente',
            'rnc' => 'RNC-PENDIENTE',
            'estado' => 'pendiente',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login')
            ->assertSessionHasErrors('email');
        $this->assertStringContainsString('pendiente de aprobación', session('errors')->first('email'));
    }

    public function test_approved_companies_can_authenticate(): void
    {
        $user = User::factory()->create(['rol' => 'empresa']);
        Empresa::create([
            'user_id' => $user->id,
            'nombre_empresa' => 'Empresa Aprobada',
            'rnc' => 'RNC-APROBADA',
            'estado' => 'aprobada',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
