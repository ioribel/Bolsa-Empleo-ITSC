<?php

namespace App\Http\Requests\Estudiante;

class ExperienciaRequest extends StudentProfileRequest
{
    protected function prepareForValidation(): void
    {
        $this->sanitizeText(['empresa', 'cargo', 'ubicacion', 'descripcion']);
    }

    public function rules(): array
    {
        return [
            'empresa' => ['required', 'string', 'max:255'],
            'cargo' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'in:empleo,pasantia,voluntariado,freelance'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio', 'prohibited_if:actual,1'],
            'actual' => ['sometimes', 'boolean'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
