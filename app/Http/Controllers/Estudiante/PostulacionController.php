<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Vacante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostulacionController extends Controller
{
    public function index()
    {
        $postulaciones = auth()->user()->estudiante->postulaciones()
            ->with('vacante.empresa')
            ->latest()
            ->get();

        return view('estudiante.postulaciones.index', compact('postulaciones'));
    }

    public function store(Request $request, string $vacanteId)
    {
        $estudiante = auth()->user()->estudiante;
        $vacante = Vacante::where('estado', 'publicada')->findOrFail($vacanteId);

        $dirigidaASuCarrera = $vacante->carreras()->where('carreras.id', $estudiante->carrera_id)->exists();
        if (! $dirigidaASuCarrera) {
            abort(403, 'Esta vacante no está dirigida a tu carrera.');
        }

        $cvPerfilDisk = null;
        if ($estudiante->cv_path) {
            foreach (['local', 'public'] as $disk) {
                if (Storage::disk($disk)->exists($estudiante->cv_path)) {
                    $cvPerfilDisk = $disk;
                    break;
                }
            }
        }

        $request->validate([
            'carta_presentacion' => ['nullable', 'string', 'max:2000'],
            'cv' => [$cvPerfilDisk ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        $yaExiste = $vacante->postulaciones()->where('estudiante_id', $estudiante->id)->exists();
        if ($yaExiste) {
            return back()->with('error', 'Ya te has postulado a esta vacante.');
        }

        $cvPath = $request->file('cv')?->store('cvs/postulaciones', 'local');
        if (! $cvPath && $cvPerfilDisk) {
            $cvPath = 'cvs/postulaciones/'.Str::uuid().'.pdf';
            Storage::disk('local')->put(
                $cvPath,
                Storage::disk($cvPerfilDisk)->get($estudiante->cv_path)
            );
        }

        $vacante->postulaciones()->create([
            'estudiante_id' => $estudiante->id,
            'carta_presentacion' => $request->carta_presentacion,
            'cv_path' => $cvPath,
            'estado' => 'recibida',
        ]);

        return redirect()->route('estudiante.vacantes.show', $vacante)
            ->with('status', 'Te has postulado exitosamente.');
    }
}