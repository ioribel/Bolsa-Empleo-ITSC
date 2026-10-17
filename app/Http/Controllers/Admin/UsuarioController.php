<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:255'],
            'rol' => ['nullable', 'in:admin,estudiante'],
            'estado' => ['nullable', 'in:activo,bloqueado'],
        ]);

        $usuarios = User::where('id', '!=', Auth::id())
            ->where('rol', '!=', 'empresa')
            ->when($filters['buscar'] ?? null, function ($query, $buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('name', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%");
                });
            })
            ->when($filters['rol'] ?? null, fn ($query, $rol) => $query->where('rol', $rol))
            ->when($filters['estado'] ?? null, function ($query, $estado) {
                $estado === 'activo'
                    ? $query->whereNotNull('email_verified_at')
                    : $query->whereNull('email_verified_at');
            })
            ->orderBy('rol')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.usuarios.index', compact('usuarios', 'filters'));
    }

    public function bloquear(string $id)
    {
        $usuario = User::findOrFail($id);
        abort_if($usuario->rol === 'empresa', 404);
        $usuario->forceFill(['email_verified_at' => null])->save();

        return back()->with('status', "Usuario {$usuario->name} bloqueado.");
    }

    public function activar(string $id)
    {
        $usuario = User::findOrFail($id);
        abort_if($usuario->rol === 'empresa', 404);
        $usuario->forceFill(['email_verified_at' => now()])->save();

        return back()->with('status', "Usuario {$usuario->name} activado.");
    }
}