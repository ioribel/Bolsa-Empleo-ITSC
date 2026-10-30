<?php

namespace App\Http\Requests\Estudiante;

use Illuminate\Validation\Rule;

class IdiomaRequest extends StudentProfileRequest
{
    protected function prepareForValidation(): void
    {
        $this->sanitizeText(['idioma']);
        if (is_string($this->input('idioma'))) {
            $this->merge(['idioma' => mb_strtolower(preg_replace('/\s+/u', ' ', trim($this->input('idioma'))) ?? '')]);
        }
    }

    public function rules(): array
    {
        $studentId = $this->user()->estudiante->id;

        return [
            'idioma' => [
                'required',
                'string',
                'max:100',
                Rule::unique('estudiante_idiomas', 'idioma')
                    ->where('estudiante_id', $studentId)
                    ->ignore($this->route('idioma')),
            ],
            'nivel' => ['required', 'in:básico,intermedio,avanzado,nativo'],
        ];
    }
}
