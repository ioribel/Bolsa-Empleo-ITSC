<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailChangeVerificationController extends Controller
{
    public function __invoke(Request $request, User $user, string $hash): RedirectResponse
    {
        abort_unless($request->user()->is($user), 403);

        $confirmed = DB::transaction(function () use ($user, $hash): bool {
            $user = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $pendingEmail = $user->pending_email;

            abort_unless(
                is_string($pendingEmail) && hash_equals(sha1($pendingEmail), $hash),
                403
            );

            $emailIsTaken = User::query()
                ->where('email', $pendingEmail)
                ->where('id', '!=', $user->getKey())
                ->exists();

            if ($emailIsTaken) {
                return false;
            }

            $user->forceFill([
                'email' => $pendingEmail,
                'pending_email' => null,
                'email_verified_at' => now(),
            ])->save();

            return true;
        });

        if (! $confirmed) {
            return redirect()->route('profile.edit')->withErrors([
                'email' => 'Ese correo ya está asociado a otra cuenta. Solicita el cambio con otra dirección.',
            ]);
        }

        return redirect()->route('profile.edit')->with('status', 'email-updated');
    }
}
