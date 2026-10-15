<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Vacante;
use Illuminate\Support\Facades\Storage;

class VacanteController extends Controller
{
    public function index()
    {
        $estudiante = auth()->user()->estudiante;

        $vacantes = Vacante::where('estado', 'publicada')
            ->whereHas('carreras', fn ($q) => $q->where('carreras.id', $estudiante->carrera_id))
            ->with('empresa')
            ->latest()
            ->get();

        return view('estudiante.vacantes.index', compact('vacantes'));
    }

    public function show(string $id)
    {
        $estudiante = auth()->user()->estudiante;

        $vacante = Vacante::where('estado', 'publicada')
            ->with(['empresa', 'carreras'])
            ->findOrFail($id);

        $yaPostulado = $vacante->postulaciones()->where('estudiante_id', $estudiante->id)->exists();
        $tieneCvPerfil = false;

        if ($estudiante->cv_path) {
            foreach (['local', 'public'] as $disk) {
                if (Storage::disk($disk)->exists($estudiante->cv_path)) {
                    $tieneCvPerfil = true;
                    break;
                }
            }
        }

        return view('estudiante.vacantes.show', compact('vacante', 'yaPostulado', 'tieneCvPerfil'));
    }
}