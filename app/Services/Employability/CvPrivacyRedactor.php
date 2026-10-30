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
            $estudiante->telefono,
        ];

        foreach ($personalValues as $value) {
            if (filled($value)) {
                $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($value, '/').'(?![\p{L}\p{N}])/iu';
                $text = preg_replace($pattern, '[DATO PERSONAL OMITIDO]', $text);
            }
        }

        $text = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/iu', '[CORREO OMITIDO]', $text);
        $text = preg_replace('/(?<!\w)\+?\d[\d\s().-]{7,}\d(?!\w)/u', '[TELÉFONO OMITIDO]', $text);
        $text = preg_replace('~(?:https?://|ftp://|www\.)\S+~iu', '[URL OMITIDA]', $text);
        $text = preg_replace('~\b(?:[a-z0-9-]+\.)+(?:com|org|net|edu|gov|io|co|app|dev|do|info|me|biz)(?:/\S*)?\b~iu', '[URL OMITIDA]', $text);

        return preg_replace('/\s+/u', ' ', trim($text));
    }

    public function redactProfile(string $text, Estudiante $estudiante): string
    {
        $lines = preg_split('/\R/u', $text) ?: [];

        return implode("\n", array_map(
            fn (string $line): string => $this->redact($line, $estudiante),
            $lines,
        ));
    }
}
