<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Estudiante\UpdateStudentProfileRequest;
use App\Models\Estudiante;
use App\Models\Habilidad;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    public function edit()
    {
        $estudiante = $this->student();
        $estudiante->load([
            'formaciones' => fn ($query) => $query->orderByDesc('fecha_inicio'),
            'experiencias' => fn ($query) => $query->orderByDesc('fecha_inicio'),
            'habilidades' => fn ($query) => $query->orderBy('nombre'),
            'idiomas' => fn ($query) => $query->orderBy('idioma'),
            'certificaciones' => fn ($query) => $query->orderByDesc('fecha_emision'),
            'carrera',
            'user',
        ]);
        $catalogoHabilidades = Habilidad::query()->orderBy('nombre')->get(['id', 'nombre', 'tipo']);
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

        return view('estudiante.perfil.edit', compact('estudiante', 'cvDetails', 'catalogoHabilidades'));
    }

    public function cv()
    {
        $estudiante = $this->student();
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

    public function update(UpdateStudentProfileRequest $request)
    {
        $estudiante = $this->student();
        $data = $request->validated();
        $estudiante->fill(Arr::only($data, [
            'telefono', 'resumen', 'titular', 'linkedin_url', 'github_url', 'portafolio_url',
        ]));

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
        $estudiante = $this->student();

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

    private function student(): Estudiante
    {
        $student = auth()->user()?->estudiante;
        abort_if(! $student, 403, 'No existe un perfil de estudiante para esta cuenta.');

        return $student;
    }
}
