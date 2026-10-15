<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    public function edit()
    {
        $estudiante = auth()->user()->estudiante;

        return view('estudiante.perfil.edit', compact('estudiante'));
    }

    public function cv()
    {
        $estudiante = auth()->user()->estudiante;
        abort_unless($estudiante->cv_path, 404);

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($estudiante->cv_path)) {
                return Storage::disk($disk)->response($estudiante->cv_path, 'curriculum.pdf', [
                    'Content-Type' => 'application/pdf',
                ]);
            }
        }

        abort(404);
    }

    public function update(Request $request)
    {
        $estudiante = auth()->user()->estudiante;

        $request->validate([
            'telefono' => ['nullable', 'string', 'max:20'],
            'resumen' => ['nullable', 'string', 'max:1000'],
            'cv' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        $estudiante->telefono = $request->telefono;
        $estudiante->resumen = $request->resumen;

        if ($request->hasFile('cv')) {
            $previousCvPath = $estudiante->cv_path;
            $estudiante->cv_path = $request->file('cv')->store('cvs/perfiles', 'local');

            if ($previousCvPath) {
                foreach (['local', 'public'] as $disk) {
                    if (Storage::disk($disk)->exists($previousCvPath)) {
                        Storage::disk($disk)->delete($previousCvPath);
                        break;
                    }
                }
            }
        }

        $estudiante->save();

        return back()->with('status', 'Perfil actualizado.');
    }
}