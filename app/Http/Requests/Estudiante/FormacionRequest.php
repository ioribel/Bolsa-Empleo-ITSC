<?php

namespace App\Http\Requests\Estudiante;

class FormacionRequest extends StudentProfileRequest
{
    protected function prepareForValidation(): void
    {
        $this->sanitizeText(['institucion', 'titulo', 'campo_estudio', 'descripcion']);
    }

    public function rules(): array
    {
        return [
            'institucion' => ['required', 'string', 'max:255'],
            'titulo' => ['required', 'string', 'max:255'],
            'nivel' => ['required', 'in:bachiller,tecnico,grado,postgrado,diplomado,otro'],
            'campo_estudio' => ['nullable', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio', 'prohibited_if:en_curso,1'],
            'en_curso' => ['sometimes', 'boolean'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
