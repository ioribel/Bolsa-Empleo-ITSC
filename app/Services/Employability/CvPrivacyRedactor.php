<?php

namespace App\Services\Employability;

use App\Models\Estudiante;

class CvPrivacyRedactor
{
    public function redact(string $text, Estudiante $estudiante): string
    {
        $personalValues = [
            $estudiante->user?->name,
            $estudiante->user?->email,
            $estudiante->matricula,
        ];

        foreach ($personalValues as $value) {
            if (filled($value)) {
                $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($value, '/').'(?![\p{L}\p{N}])/iu';
                $text = preg_replace($pattern, '[DATO PERSONAL OMITIDO]', $text);
            }
        }

        $text = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/iu', '[CORREO OMITIDO]', $text);
        $text = preg_replace('/(?<!\w)\+?\d[\d\s().-]{7,}\d(?!\w)/u', '[TELÉFONO OMITIDO]', $text);

        return preg_replace('/\s+/u', ' ', trim($text));
    }
}
