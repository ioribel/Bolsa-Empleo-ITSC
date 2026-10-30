<?php

namespace App\Http\Requests\Estudiante;

use Illuminate\Foundation\Http\FormRequest;

abstract class StudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->rol === 'estudiante'
            && $this->user()->estudiante()->exists();
    }

    /**
     * Strip markup and surrounding whitespace from user-entered text fields.
     *
     * @param  list<string>  $fields
     */
    protected function sanitizeText(array $fields): void
    {
        $clean = [];

        foreach ($fields as $field) {
            $value = $this->input($field);
            if (is_string($value)) {
                $clean[$field] = trim(strip_tags($value));
            }
        }

        $this->merge($clean);
    }

    public function messages(): array
    {
        return [
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'fecha_fin.prohibited_if' => 'No indiques una fecha de fin si la actividad continúa actualmente.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser posterior o igual a la fecha de emisión.',
            'idioma.unique' => 'Ya registraste este idioma en tu perfil.',
            'linkedin_url.url' => 'Ingresa una URL HTTPS válida de LinkedIn.',
            'linkedin_url.*' => 'Ingresa una URL de LinkedIn válida.',
            'github_url.url' => 'Ingresa una URL HTTPS válida de GitHub.',
            'github_url.*' => 'Ingresa una URL de GitHub válida.',
            'portafolio_url.url' => 'Ingresa una URL HTTPS válida para tu portafolio.',
            'url_credencial.url' => 'Ingresa una URL HTTPS válida para la credencial.',
        ];
    }
}
