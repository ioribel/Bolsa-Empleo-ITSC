<?php

namespace App\Http\Requests\Estudiante;

use App\Support\StudentProfileUrl;

class UpdateStudentProfileRequest extends StudentProfileRequest
{
    protected function prepareForValidation(): void
    {
        $this->sanitizeText([
            'telefono', 'resumen', 'titular', 'linkedin_url', 'github_url', 'portafolio_url',
        ]);
    }

    public function rules(): array
    {
        return [
            'telefono' => ['nullable', 'string', 'max:20'],
            'resumen' => ['nullable', 'string', 'max:1000'],
            'titular' => ['nullable', 'string', 'max:120'],
            'linkedin_url' => ['nullable', 'url:https', 'max:255', StudentProfileUrl::onDomain('linkedin.com')],
            'github_url' => ['nullable', 'url:https', 'max:255', StudentProfileUrl::onDomain('github.com')],
            'portafolio_url' => ['nullable', 'url:https', 'max:255'],
            'cv' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ];
    }
}
