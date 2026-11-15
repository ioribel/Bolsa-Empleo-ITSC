<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyPendingEmailChange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated_without_changing_email(): void
    {
        $user = User::factory()->create();
        $email = $user->email;

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Updated Name',
                'email' => $email,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame($email, $user->fresh()->email);
        $this->assertNull($user->fresh()->pending_email);
    }

    public function test_changing_email_does_not_deactivate_the_account(): void
    {
        $user = User::factory()->create();
        $verifiedAt = $user->email_verified_at;

        Notification::fake();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'current_password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->pending_email);
        $this->assertNotSame('test@example.com', $user->email);
        $this->assertSame($verifiedAt->toDateTimeString(), $user->email_verified_at->toDateTimeString());
        $this->assertTrue($user->activo);

        Notification::assertSentOnDemand(VerifyPendingEmailChange::class);

        $this->post('/logout');
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_resend_pending_email_verification_after_confirming_password(): void
    {
        $user = User::factory()->create(['pending_email' => 'new@example.com']);

        Notification::fake();

        $this->actingAs($user)
            ->post('/profile/email-change/resend', [
                'current_password' => 'password',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        Notification::assertSentOnDemand(VerifyPendingEmailChange::class);
    }

    public function test_email_change_requires_the_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => $user->name,
                'email' => 'new@example.com',
            ])
            ->assertSessionHasErrors('current_password')
            ->assertRedirect('/profile');

        $this->assertSame($user->email, $user->fresh()->email);
        $this->assertNull($user->fresh()->pending_email);
    }

    public function test_pending_email_changes_for_student_company_and_admin_are_confirmed_by_signed_link(): void
    {
        Notification::fake();

        foreach (['estudiante', 'empresa', 'admin'] as $role) {
            $user = User::factory()->create(['rol' => $role]);
            $newEmail = "{$role}@example.com";

            $this->actingAs($user)
                ->patch('/profile', [
                    'name' => $user->name,
                    'email' => $newEmail,
                    'current_password' => 'password',
                ])
                ->assertSessionHasNoErrors()
                ->assertRedirect('/profile');

            $notification = Notification::sent(
                new AnonymousNotifiable,
                VerifyPendingEmailChange::class
            )->last();

            $this->assertNotNull($notification);

            $this->get($notification->verificationUrl)
                ->assertRedirect(route('profile.edit', absolute: false))
                ->assertSessionHas('status', 'email-updated');

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'email' => $newEmail,
                'pending_email' => null,
            ]);
            $this->assertNotNull($user->fresh()->email_verified_at);
        }
    }

    public function test_email_change_verification_requires_the_requesting_user_to_be_authenticated(): void
    {
        $user = User::factory()->create(['pending_email' => 'new@example.com']);
        $otherUser = User::factory()->create();
        $url = URL::temporarySignedRoute(
            'email.change.verify',
            now()->addHour(),
            ['user' => $user->id, 'hash' => sha1('new@example.com')]
        );

        $this->actingAs($otherUser)
            ->get($url)
            ->assertForbidden();

        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_email_change_verification_rejects_tampered_and_expired_links(): void
    {
        $user = User::factory()->create(['pending_email' => 'new@example.com']);

        $this->actingAs($user)
            ->get(URL::temporarySignedRoute(
                'email.change.verify',
                now()->addHour(),
                ['user' => $user->id, 'hash' => sha1('other@example.com')]
            ))
            ->assertForbidden();

        $expiredUrl = URL::temporarySignedRoute(
            'email.change.verify',
            now()->subMinute(),
            ['user' => $user->id, 'hash' => sha1('new@example.com')]
        );

        $this->get($expiredUrl)->assertForbidden();
        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
