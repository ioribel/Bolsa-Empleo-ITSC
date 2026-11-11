<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Models\User;
use App\Notifications\AdminCreatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AdminManagementController extends Controller
{
    public function create(): View
    {
        return view('admin.create');
    }

    public function store(StoreAdminRequest $request): RedirectResponse
    {
        $admin = DB::transaction(function () use ($request): User {
            $admin = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
                'rol' => 'admin',
            ]);

            $admin->forceFill(['email_verified_at' => now()])->save();

            return $admin;
        });

        $token = Password::createToken($admin);
        $admin->notify(new AdminCreatedNotification($token));

        return redirect()
            ->route('admin.usuarios.index')
            ->with('status', 'Administrador creado. Se envió un correo de bienvenida con un enlace para cambiar la contraseña.');
    }
}
