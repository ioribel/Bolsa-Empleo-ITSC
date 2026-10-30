<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Postulacion;
use Illuminate\Support\Facades\Storage;

class PostulacionController extends Controller
{
    public function index(string $vacanteId)
    {
        $vacante = auth()->user()->empresa->vacantes()->findOrFail($vacanteId);

        $postulaciones = $vacante->postulaciones()
            ->with([
                'estudiante.user',
                'estudiante.carrera',
                'estudiante.formaciones',
                'estudiante.experiencias',
                'estudiante.habilidades',
                'estudiante.idiomas',
                'estudiante.certificaciones',
            ])
            ->latest()
            ->get();

        return view('empresa.postulaciones.index', compact('vacante', 'postulaciones'));
    }

    public function cv(string $id)
    {
        $postulacion = $this->propia($id)->load('estudiante');
        $cvPath = $postulacion->cv_path ?: $postulacion->estudiante->cv_path;
        abort_unless($cvPath, 404);

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($cvPath)) {
                return Storage::disk($disk)->response($cvPath, 'curriculum.pdf', [
                    'Content-Type' => 'application/pdf',
                ]);
            }
        }

        abort(404);
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
