<?php

declare(strict_types=1);

namespace App\Core\Security;

final class Sanitizer
{
    public static function string(mixed $value): string
    {
        return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
    }

    public static function array(array $input): array
    {
        return array_map(
            fn ($v) => is_array($v) ? self::array($v) : self::string($v),
            $input
        );
    }
}
