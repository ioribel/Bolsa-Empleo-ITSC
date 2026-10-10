<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class EmpresaRegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register-empresa');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nombre_empresa' => ['required', 'string', 'max:255'],
            'rnc' => ['required', 'string', 'max:20', 'unique:empresas,rnc'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'rol' => 'empresa',
                'email_verified_at' => now(),
            ]);

            $user->empresa()->create([
                'nombre_empresa' => $request->nombre_empresa,
                'rnc' => $request->rnc,
                'telefono' => $request->telefono,
                'estado' => 'pendiente',
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}