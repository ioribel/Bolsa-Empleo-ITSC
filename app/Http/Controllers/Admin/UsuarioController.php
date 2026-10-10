<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::where('id', '!=', auth()->id())->orderBy('rol')->orderBy('name')->get();

        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function bloquear(string $id)
{
    $usuario = User::findOrFail($id);
    $usuario->forceFill(['email_verified_at' => null])->save();

    return back()->with('status', "Usuario {$usuario->name} bloqueado.");
}

    public function activar(string $id)
    {
        $usuario = User::findOrFail($id);
        $usuario->forceFill(['email_verified_at' => now()])->save();

        return back()->with('status', "Usuario {$usuario->name} activado.");
    }
}