<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Postulacion;

class PostulacionController extends Controller
{
    public function index(string $vacanteId)
    {
        $vacante = auth()->user()->empresa->vacantes()->findOrFail($vacanteId);

        $postulaciones = $vacante->postulaciones()
            ->with('estudiante.user', 'estudiante.carrera')
            ->latest()
            ->get();

        return view('empresa.postulaciones.index', compact('vacante', 'postulaciones'));
    }

    private function propia(string $id): Postulacion
    {
        return Postulacion::whereHas('vacante', function ($q) {
            $q->where('empresa_id', auth()->user()->empresa->id);
        })->findOrFail($id);
    }

    public function enRevision(string $id)
    {
        $postulacion = $this->propia($id);

        try {
            $postulacion->marcarEnRevision();
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Postulación puesta en revisión.');
    }

    public function aceptar(string $id)
    {
        $postulacion = $this->propia($id);

        try {
            $postulacion->aceptar();
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Postulación aceptada.');
    }

    public function rechazar(string $id)
    {
        $postulacion = $this->propia($id);

        try {
            $postulacion->rechazar();
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Postulación rechazada.');
    }
}