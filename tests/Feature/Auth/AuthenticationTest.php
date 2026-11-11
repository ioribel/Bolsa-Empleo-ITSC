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

        $response->assertStatus(200)
            ->assertSee('Inicia sesión')
            ->assertSee('Correo electrónico')
            ->assertSee('Contraseña')
            ->assertDontSee('Log In');
    }

    public function test_welcome_page_remains_available_to_guests(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Conectamos el talento del ITSC');
    }

    public function test_authenticated_users_are_redirected_away_from_welcome_to_their_role_dashboard(): void
    {
        $dashboards = [
            'admin' => route('admin.dashboard'),
            'empresa' => route('empresa.dashboard'),
            'estudiante' => route('estudiante.dashboard'),
        ];

        foreach ($dashboards as $role => $dashboard) {
            $user = User::factory()->create(['rol' => $role]);

            $this->actingAs($user)
                ->get('/')
                ->assertRedirect($dashboard);
        }
    }

    public function test_welcome_page_reloads_when_restored_from_browser_back_forward_cache(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('event.persisted')
            ->assertSee('window.location.reload()');
    }

    public function test_authenticated_users_are_redirected_from_login_to_their_role_dashboard(): void
    {
        $dashboards = [
            'admin' => route('admin.dashboard'),
            'empresa' => route('empresa.dashboard'),
            'estudiante' => route('estudiante.dashboard'),
        ];

        foreach ($dashboards as $role => $dashboard) {
            $user = User::factory()->create(['rol' => $role]);

            $this->actingAs($user)
                ->get('/login')
                ->assertRedirect($dashboard);
        }
    }

    public function test_login_page_reloads_when_restored_from_browser_back_forward_cache(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('event.persisted')
            ->assertSee('window.location.reload()');
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

        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_pages_cannot_be_cached(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk()
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('Expires', '0');

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
    }
}
