<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Notifications\EmpresaAprobadaNotification;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'in:pendiente,aprobada,rechazada,bloqueada'],
        ]);

        $empresas = Empresa::with('user')
            ->when($filters['buscar'] ?? null, function ($query, $buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('nombre_empresa', 'like', "%{$buscar}%")
                        ->orWhere('rnc', 'like', "%{$buscar}%")
                        ->orWhereHas('user', fn ($user) => $user->where('email', 'like', "%{$buscar}%"));
                });
            })
            ->when($filters['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.empresas.index', compact('empresas', 'filters'));
    }

    public function aprobar(string $id)
    {
        $empresa = Empresa::findOrFail($id);
        $usuario = $empresa->user;

        if ($empresa->estado !== 'aprobada') {
            $empresa->update(['estado' => 'aprobada']);
            $usuario->notify(new EmpresaAprobadaNotification);
        }

        if ($usuario->email_verified_at === null) {
            $usuario->forceFill(['email_verified_at' => now()])->save();
        }

        return back()->with('status', "Empresa {$empresa->nombre_empresa} aprobada.");
    }

    public function bloquear(string $id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->update(['estado' => 'bloqueada']);

        return back()->with('status', "Empresa {$empresa->nombre_empresa} bloqueada.");
    }

    public function rechazar(string $id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->update(['estado' => 'rechazada']);

        return back()->with('status', "Empresa {$empresa->nombre_empresa} rechazada.");
    }
}