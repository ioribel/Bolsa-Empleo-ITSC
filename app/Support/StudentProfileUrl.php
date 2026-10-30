<?php

namespace App\Support;

use Closure;

class StudentProfileUrl
{
    public static function onDomain(string $domain): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail) use ($domain): void {
            $host = strtolower((string) parse_url((string) $value, PHP_URL_HOST));

            if ($host !== $domain && ! str_ends_with($host, '.'.$domain)) {
                $fail("El campo {$attribute} debe pertenecer a {$domain}.");
            }
        };
    }
}
