<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresas = Empresa::with('user')->latest()->get();

        return view('admin.empresas.index', compact('empresas'));
    }

    public function aprobar(string $id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->update(['estado' => 'aprobada']);

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