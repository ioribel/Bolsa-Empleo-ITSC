<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AdminCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_another_admin_and_send_a_welcome_notification(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.administradores.create'))
            ->assertOk()
            ->assertSee('Crear administrador');

        $this->actingAs($admin)
            ->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertSee(route('admin.administradores.create'))
            ->assertSee('flex justify-end');

        $this->actingAs($admin)
            ->post(route('admin.administradores.store'), [
                'name' => 'Nueva Administradora',
                'email' => 'nueva.admin@example.com',
                'password' => 'AdminPass123',
                'password_confirmation' => 'AdminPass123',
            ])
            ->assertRedirect(route('admin.usuarios.index'))
            ->assertSessionHas('status');

        $newAdmin = User::where('email', 'nueva.admin@example.com')->firstOrFail();

        $this->assertSame('admin', $newAdmin->rol);
        $this->assertTrue(Hash::check('AdminPass123', $newAdmin->password));
        $this->assertNotNull($newAdmin->email_verified_at);
        Notification::assertSentTo(
            $newAdmin,
            AdminCreatedNotification::class,
            function (AdminCreatedNotification $notification) use ($newAdmin): bool {
                $mail = $notification->toMail($newAdmin);

                return $mail->viewData['passwordUrl'] === route('password.reset', [
                    'token' => $notification->token,
                    'email' => $newAdmin->email,
                ]) && str_contains((string) $mail->render(), 'target="_blank"');
            },
        );
    }

    public function test_admin_invitation_password_link_works_without_replacing_the_current_admin_session(): void
    {
        $loggedInAdmin = User::factory()->create(['rol' => 'admin']);
        $invitedAdmin = User::factory()->create(['rol' => 'admin']);
        $token = Password::createToken($invitedAdmin);

        $this->actingAs($loggedInAdmin)
            ->get(route('password.reset', [
                'token' => $token,
                'email' => $invitedAdmin->email,
            ]))
            ->assertOk()
            ->assertSee('Restablece tu contraseña');

        $this->actingAs($loggedInAdmin)
            ->post(route('password.store'), [
                'token' => $token,
                'email' => $invitedAdmin->email,
                'password' => 'NewAdminPass123',
                'password_confirmation' => 'NewAdminPass123',
            ])
            ->assertRedirect(route('password.reset.success'));

        $this->assertAuthenticatedAs($loggedInAdmin);
        $this->assertTrue(Hash::check('NewAdminPass123', $invitedAdmin->fresh()->password));
    }

    public function test_only_admins_can_open_or_submit_admin_creation(): void
    {
        $student = User::factory()->create(['rol' => 'estudiante']);

        $this->get(route('admin.administradores.create'))->assertRedirect(route('login'));
        $this->post(route('admin.administradores.store'))->assertRedirect(route('login'));

        $this->actingAs($student)
            ->get(route('admin.administradores.create'))
            ->assertForbidden();

        $this->post(route('admin.administradores.store'), [
            'name' => 'No autorizado',
            'email' => 'no.autorizado@example.com',
            'password' => 'AdminPass123',
            'password_confirmation' => 'AdminPass123',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'no.autorizado@example.com']);
    }

    public function test_admin_creation_validates_unique_email_confirmation_and_password_complexity(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin);

        $this->from(route('admin.administradores.create'))
            ->post(route('admin.administradores.store'), [
                'name' => 'Nuevo Administrador',
                'email' => $admin->email,
                'password' => 'AdminPass123',
                'password_confirmation' => 'AdminPass123',
            ])
            ->assertSessionHasErrors('email');

        $this->from(route('admin.administradores.create'))
            ->post(route('admin.administradores.store'), [
                'name' => 'Nuevo Administrador',
                'email' => 'nuevo.admin@example.com',
                'password' => 'lettersOnly',
                'password_confirmation' => 'lettersOnly',
            ])
            ->assertSessionHasErrors('password');

        $this->from(route('admin.administradores.create'))
            ->post(route('admin.administradores.store'), [
                'name' => 'Nuevo Administrador',
                'email' => 'nuevo.admin@example.com',
                'password' => 'AdminPass123',
                'password_confirmation' => 'DifferentPass123',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 1);
    }
}
