<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVacanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->empresa?->estado === 'aprobada';
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'requisitos' => ['nullable', 'string'],
            'tipo_empleo' => ['required', 'in:tiempo_completo,medio_tiempo,pasantia'],
            'modalidad' => ['required', 'in:presencial,remoto,hibrido'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'salario_min' => ['nullable', 'numeric', 'min:0'],
            'salario_max' => ['nullable', 'numeric', 'gte:salario_min'],
            'fecha_cierre' => ['nullable', 'date', 'after:today'],
            'carreras' => ['required', 'array', 'min:1'],
            'carreras.*' => ['exists:carreras,id'],
        ];
    }
}
