<?php

namespace App\Http\Requests\Estudiante;

class CertificacionRequest extends StudentProfileRequest
{
    protected function prepareForValidation(): void
    {
        $this->sanitizeText(['nombre', 'entidad_emisora']);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'entidad_emisora' => ['required', 'string', 'max:255'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'url_credencial' => ['nullable', 'url:https', 'max:255'],
        ];
    }
}
