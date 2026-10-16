<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertSessionHas('status', 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.')
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            return $notification->locale === 'es';
        });
    }

    public function test_unknown_email_receives_the_same_confirmation_without_a_notification(): void
    {
        Notification::fake();

        $response = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'missing@example.com']);

        $response->assertRedirect('/forgot-password')
            ->assertSessionHas('status', 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.')
            ->assertSessionHasNoErrors();
        Notification::assertNothingSent();
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            $this->assertGuest();
            $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

            return true;
        });
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $originalPassword = $user->password;

        $response = $this->from('/reset-password/invalid-token')->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect('/reset-password/invalid-token')
            ->assertSessionHasErrors('email');
        $this->assertSame($originalPassword, $user->refresh()->password);
    }

    public function test_expired_token_is_rejected(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $originalPassword = $user->password;

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user, $originalPassword) {
            DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->update(['created_at' => now()->subMinutes(config('auth.passwords.users.expire') + 1)]);

            $response = $this->from('/reset-password/'.$notification->token)->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

            $response->assertRedirect('/reset-password/'.$notification->token)
                ->assertSessionHasErrors('email');
            $this->assertSame($originalPassword, $user->refresh()->password);

            return true;
        });
    }

    public function test_new_password_must_be_at_least_eight_characters(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

            $response->assertSessionHasErrors('password');

            return true;
        });
    }
}
