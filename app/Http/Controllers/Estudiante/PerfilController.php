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
        $cvDetails = null;

        if ($estudiante->cv_path) {
            foreach (['local', 'public'] as $disk) {
                if (Storage::disk($disk)->exists($estudiante->cv_path)) {
                    $cvDetails = [
                        'name' => $estudiante->cv_original_name ?: basename($estudiante->cv_path),
                        'size' => Storage::disk($disk)->size($estudiante->cv_path),
                    ];

                    break;
                }
            }
        }

        return view('estudiante.perfil.edit', compact('estudiante', 'cvDetails'));
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
            $uploadedCv = $request->file('cv');
            $newCvPath = $uploadedCv->store('cvs/perfiles', 'local');

            if (! $newCvPath) {
                return back()->with('error', 'No se pudo guardar el currículum. Inténtalo nuevamente.');
            }

            $estudiante->cv_path = $newCvPath;
            $estudiante->cv_original_name = $this->safeOriginalName($uploadedCv->getClientOriginalName());

            if ($previousCvPath && $previousCvPath !== $newCvPath) {
                foreach (['local', 'public'] as $disk) {
                    if (Storage::disk($disk)->exists($previousCvPath)) {
                        Storage::disk($disk)->delete($previousCvPath);
                    }
                }
            }
        }

        $estudiante->save();

        return back()->with('status', 'Perfil actualizado.');
    }

    public function destroyCv()
    {
        $estudiante = auth()->user()->estudiante;

        if ($estudiante->cv_path) {
            foreach (['local', 'public'] as $disk) {
                if (Storage::disk($disk)->exists($estudiante->cv_path)
                    && ! Storage::disk($disk)->delete($estudiante->cv_path)) {
                    return back()->with('error', 'No se pudo eliminar el archivo del currículum. Inténtalo nuevamente.');
                }
            }
        }

        $estudiante->forceFill([
            'cv_path' => null,
            'cv_original_name' => null,
        ])->save();

        return back()->with('status', 'Currículum eliminado.');
    }

    private function safeOriginalName(string $name): string
    {
        $name = basename($name);
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name);

        return mb_substr(trim($name) ?: 'curriculum.pdf', 0, 255);
    }
}