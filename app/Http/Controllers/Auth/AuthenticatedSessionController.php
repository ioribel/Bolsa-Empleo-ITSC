<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {

        $request->authenticate();

        $user = Auth::user();

        if ($user->rol === 'empresa' && $user->empresa?->estado !== 'aprobada') {
            $estado = $user->empresa?->estado;
            $message = match ($estado) {
                'pendiente' => 'Tu perfil de empresa está pendiente de aprobación. Te avisaremos por correo cuando sea aprobado.',
                'rechazada' => 'La solicitud de tu empresa fue rechazada. Contacta con administración para más información.',
                'bloqueada' => 'La cuenta de tu empresa está bloqueada. Contacta con administración.',
                default => 'El perfil de tu empresa no está disponible. Contacta con administración.',
            };

            Auth::logout();

            throw ValidationException::withMessages(['email' => $message]);
        }

        if ($user->email_verified_at === null) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta ha sido bloqueada por el administrador.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
