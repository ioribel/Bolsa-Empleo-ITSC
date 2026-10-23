<?php

namespace App\Services\Employability;

use App\Exceptions\EmployabilityAnalysisException;
use App\Models\Vacante;

class VacancyCriteriaBuilder
{
    private const MAX_CRITERIA = 30;

    public function build(Vacante $vacante): array
    {
        $source = trim($vacante->requisitos ?? '') ?: trim($vacante->descripcion);
        $parts = preg_split('/(?:\r\n|\r|\n|(?<=[.!?;])\s+)/u', $source) ?: [];
        $criteria = [];

        foreach ($parts as $part) {
            $criterion = trim(preg_replace('/^\s*[-*•\d.)]+\s*/u', '', $part));

            if (mb_strlen($criterion) < 8) {
                continue;
            }

            if (! in_array(mb_strtolower($criterion), array_map('mb_strtolower', $criteria), true)) {
                $criteria[] = $criterion;
            }
        }

        if (count($criteria) > self::MAX_CRITERIA) {
            throw new EmployabilityAnalysisException('La vacante contiene demasiados requisitos para analizarlos en una sola solicitud.');
        }

        return array_map(
            fn (string $text, int $index): array => ['id' => 'R'.($index + 1), 'text' => $text],
            $criteria,
            array_keys($criteria),
        );
    }
}
