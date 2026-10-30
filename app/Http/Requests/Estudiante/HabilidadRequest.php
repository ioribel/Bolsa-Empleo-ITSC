<?php

namespace App\Http\Requests\Estudiante;

class HabilidadRequest extends StudentProfileRequest
{
    protected function prepareForValidation(): void
    {
        $this->sanitizeText(['nombre']);
        if (is_string($this->input('nombre'))) {
            $this->merge(['nombre' => mb_strtolower(preg_replace('/\s+/u', ' ', trim($this->input('nombre'))) ?? '')]);
        }
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:2', 'max:100'],
            'tipo' => ['required', 'in:técnica,blanda'],
            'nivel' => ['required', 'in:básico,intermedio,avanzado'],
        ];
    }
}
