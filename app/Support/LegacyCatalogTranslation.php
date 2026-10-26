<?php

namespace App\Support;

class LegacyCatalogTranslation
{
    /** Extract only unambiguous language content; never invent a translation. */
    public static function extract(?string $value): array
    {
        $value = trim($value ?? '');
        $text = trim(strip_tags($value));
        if ($text === '') {
            return [];
        }

        if (preg_match('/^([^()]+?)\s*\(([^()]+)\)$/u', $text, $parts)
            && preg_match('/\p{Arabic}/u', $parts[1])
            && ! preg_match('/[a-z]/i', $parts[1])
            && ! preg_match('/\p{Arabic}/u', $parts[2])) {
            return ['ar' => trim($parts[1]), 'en' => trim($parts[2])];
        }

        $arabic = preg_match('/\p{Arabic}/u', $text);
        $latin = preg_match('/[a-z]/i', $text);
        if ($arabic && ! $latin) {
            return ['ar' => $value];
        }
        if (! $arabic && $latin) {
            return ['en' => $value];
        }
        if (! $arabic && ! $latin) {
            return ['ar' => $value, 'en' => $value];
        }

        return [];
    }
}
