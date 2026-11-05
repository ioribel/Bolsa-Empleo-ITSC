<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Empresa;

class EmpresaController extends Controller
{
    public function show(Empresa $empresa)
    {
        $estudiante = auth()->user()->estudiante;

        abort_unless($empresa->estado === 'aprobada', 403, 'El perfil de esta empresa no está disponible en este momento.');

        $tieneVacantesDisponibles = $empresa->vacantesPublicadasParaCarrera($estudiante->carrera_id)->exists();

        abort_unless($tieneVacantesDisponibles, 403, 'No tienes acceso a este perfil porque la empresa no tiene vacantes relacionadas con tu carrera.');

        $vacantes = $empresa->vacantesPublicadasParaCarrera($estudiante->carrera_id)
            ->get();

        return view('estudiante.empresas.show', compact('empresa', 'vacantes'));
    }
}
