<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Notifications\VerifyPendingEmailChange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $emailChanged = $validated['email'] !== $user->email;

        $user->name = $validated['name'];

        if ($emailChanged) {
            $user->pending_email = $validated['email'];
        }

        $user->save();

        if ($emailChanged) {
            $this->sendPendingEmailVerification($user);

            return Redirect::route('profile.edit')->with('status', 'email-change-requested');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Resend the verification link for a pending email change.
     */
    public function resendEmailChangeVerification(Request $request): RedirectResponse
    {
        $request->validateWithBag('emailChangeResend', [
            'current_password' => ['required', 'string', 'max:255', 'current_password'],
        ]);

        $user = $request->user();

        if (! $user->pending_email) {
            return Redirect::route('profile.edit')->withErrors([
                'email' => 'No hay un cambio de correo pendiente de confirmación.',
            ]);
        }

        $this->sendPendingEmailVerification($user);

        return Redirect::route('profile.edit')->with('status', 'email-change-requested');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function sendPendingEmailVerification(User $user): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'email.change.verify',
            now()->addHour(),
            [
                'user' => $user->getKey(),
                'hash' => sha1($user->pending_email),
            ]
        );

        Notification::route('mail', $user->pending_email)
            ->notify(new VerifyPendingEmailChange($verificationUrl));
    }
}
