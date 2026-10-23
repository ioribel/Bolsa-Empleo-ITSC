<?php

namespace App\Services\Employability;

use App\Exceptions\CvExtractionException;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class CvTextExtractor
{
    private const MAX_FILE_BYTES = 2_097_152;

    private const MAX_TEXT_CHARACTERS = 60_000;

    public function extractFromStorage(string $path, string $disk = 'local'): string
    {
        if (! Storage::disk($disk)->exists($path)) {
            throw new CvExtractionException('No se encontró el currículum almacenado.');
        }

        return $this->extract(Storage::disk($disk)->get($path));
    }

    public function extract(string $contents): string
    {
        if (strlen($contents) > self::MAX_FILE_BYTES) {
            throw new CvExtractionException('El currículum supera el tamaño máximo permitido para el análisis.');
        }

        if (! str_contains(substr($contents, 0, 1024), '%PDF-')) {
            throw new CvExtractionException('El archivo no es un PDF válido.');
        }

        try {
            $document = (new Parser())->parseContent($contents);
            $pages = $document->getPages();
            $text = $document->getText();
        } catch (\Exception $exception) {
            throw new CvExtractionException('No se pudo leer el PDF. Verifica que el archivo no esté dañado.', previous: $exception);
        }

        if (count($pages) > 50) {
            throw new CvExtractionException('El currículum supera el límite de 50 páginas para el análisis.');
        }

        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);

        if ($text === '') {
            throw new CvExtractionException('El PDF no contiene texto legible. Los currículums escaneados aún no son compatibles.');
        }

        if (mb_strlen($text) > self::MAX_TEXT_CHARACTERS) {
            throw new CvExtractionException('El texto del currículum supera el límite permitido para el análisis.');
        }

        return $text;
    }
}
