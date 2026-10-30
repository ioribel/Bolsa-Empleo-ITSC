<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Estudiante\CertificacionRequest;
use App\Http\Requests\Estudiante\ExperienciaRequest;
use App\Http\Requests\Estudiante\FormacionRequest;
use App\Http\Requests\Estudiante\HabilidadRequest;
use App\Http\Requests\Estudiante\IdiomaRequest;
use App\Models\Estudiante;
use App\Models\Habilidad;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerfilProfesionalController extends Controller
{
    public function storeFormacion(FormacionRequest $request)
    {
        $student = $this->student();
        $this->ensureCapacity($student->formaciones(), 10, 'formaciones');
        $data = $request->validated();
        $data['en_curso'] = filter_var($data['en_curso'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($data['en_curso']) {
            $data['fecha_fin'] = null;
        }

        $student->formaciones()->create($data);

        return back()->with('status', 'Formación agregada.');
    }

    public function updateFormacion(FormacionRequest $request, string $formacion)
    {
        $student = $this->student();
        $record = $student->formaciones()->findOrFail($formacion);
        $data = $request->validated();
        $data['en_curso'] = filter_var($data['en_curso'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($data['en_curso']) {
            $data['fecha_fin'] = null;
        }
        $record->update($data);

        return back()->with('status', 'Formación actualizada.');
    }

    public function destroyFormacion(string $formacion)
    {
        $this->student()->formaciones()->findOrFail($formacion)->delete();

        return back()->with('status', 'Formación eliminada.');
    }

    public function storeExperiencia(ExperienciaRequest $request)
    {
        $student = $this->student();
        $this->ensureCapacity($student->experiencias(), 15, 'experiencias');
        $data = $request->validated();
        $data['actual'] = filter_var($data['actual'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($data['actual']) {
            $data['fecha_fin'] = null;
        }

        $student->experiencias()->create($data);

        return back()->with('status', 'Experiencia agregada.');
    }

    public function updateExperiencia(ExperienciaRequest $request, string $experiencia)
    {
        $student = $this->student();
        $record = $student->experiencias()->findOrFail($experiencia);
        $data = $request->validated();
        $data['actual'] = filter_var($data['actual'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($data['actual']) {
            $data['fecha_fin'] = null;
        }
        $record->update($data);

        return back()->with('status', 'Experiencia actualizada.');
    }

    public function destroyExperiencia(string $experiencia)
    {
        $this->student()->experiencias()->findOrFail($experiencia)->delete();

        return back()->with('status', 'Experiencia eliminada.');
    }

    public function storeHabilidad(HabilidadRequest $request)
    {
        $student = $this->student();
        $data = $request->validated();
        $existingSkill = Habilidad::where('nombre', $this->normalizedSkillName($data['nombre']))->first();

        if (! $existingSkill || ! $student->habilidades()->whereKey($existingSkill->id)->exists()) {
            $this->ensureCapacity($student->habilidades(), 30, 'habilidades');
        }

        $this->saveHabilidad($student, $data, $existingSkill);

        return back()->with('status', 'Habilidad agregada.');
    }

    public function updateHabilidad(HabilidadRequest $request, string $habilidad)
    {
        $student = $this->student();
        $student->habilidades()->where('habilidades.id', $habilidad)->firstOrFail();
        $data = $request->validated();
        $newSkill = $this->findOrCreateSkill($data);
        DB::transaction(function () use ($student, $habilidad, $newSkill, $data): void {
            if ((string) $habilidad !== (string) $newSkill->id) {
                $student->habilidades()->detach($habilidad);
            }
            $student->habilidades()->syncWithoutDetaching([
                $newSkill->id => ['nivel' => $data['nivel']],
            ]);
        });

        return back()->with('status', 'Habilidad actualizada.');
    }

    public function destroyHabilidad(string $habilidad)
    {
        $student = $this->student();
        $student->habilidades()->where('habilidades.id', $habilidad)->firstOrFail();
        $student->habilidades()->detach($habilidad);

        return back()->with('status', 'Habilidad eliminada.');
    }

    public function storeIdioma(IdiomaRequest $request)
    {
        $student = $this->student();
        $this->ensureCapacity($student->idiomas(), 8, 'idiomas');
        $student->idiomas()->create($request->validated());

        return back()->with('status', 'Idioma agregado.');
    }

    public function updateIdioma(IdiomaRequest $request, string $idioma)
    {
        $student = $this->student();
        $student->idiomas()->findOrFail($idioma)->update($request->validated());

        return back()->with('status', 'Idioma actualizado.');
    }

    public function destroyIdioma(string $idioma)
    {
        $this->student()->idiomas()->findOrFail($idioma)->delete();

        return back()->with('status', 'Idioma eliminado.');
    }

    public function storeCertificacion(CertificacionRequest $request)
    {
        $student = $this->student();
        $this->ensureCapacity($student->certificaciones(), 15, 'certificaciones');
        $student->certificaciones()->create($request->validated());

        return back()->with('status', 'Certificación agregada.');
    }

    public function updateCertificacion(CertificacionRequest $request, string $certificacion)
    {
        $student = $this->student();
        $student->certificaciones()->findOrFail($certificacion)->update($request->validated());

        return back()->with('status', 'Certificación actualizada.');
    }

    public function destroyCertificacion(string $certificacion)
    {
        $this->student()->certificaciones()->findOrFail($certificacion)->delete();

        return back()->with('status', 'Certificación eliminada.');
    }

    private function student(): Estudiante
    {
        $student = auth()->user()?->estudiante;
        abort_if(! $student, 403, 'No existe un perfil de estudiante para esta cuenta.');

        return $student;
    }

    private function ensureCapacity(HasMany|BelongsToMany $relation, int $limit, string $section): void
    {
        if ($relation->count() >= $limit) {
            throw ValidationException::withMessages([
                $section => "Has alcanzado el máximo de {$limit} registros para esta sección.",
            ]);
        }
    }

    /**
     * @param  array{nombre:string,tipo:string,nivel:string}  $data
     */
    private function saveHabilidad(Estudiante $student, array $data, ?Habilidad $skill = null): void
    {
        $skill ??= $this->findOrCreateSkill($data);
        if ($student->habilidades()->where('habilidades.id', $skill->id)->exists()) {
            $student->habilidades()->updateExistingPivot($skill->id, ['nivel' => $data['nivel']]);

            return;
        }

        $student->habilidades()->attach($skill->id, ['nivel' => $data['nivel']]);
    }

    /**
     * @param  array{nombre:string,tipo:string,nivel:string}  $data
     */
    private function findOrCreateSkill(array $data): Habilidad
    {
        return Habilidad::firstOrCreate(
            ['nombre' => $this->normalizedSkillName($data['nombre'])],
            ['tipo' => $data['tipo']],
        );
    }

    private function normalizedSkillName(string $name): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($name)) ?? '');
    }
}
