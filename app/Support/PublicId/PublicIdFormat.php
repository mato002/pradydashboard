<?php

namespace App\Support\PublicId;

final class PublicIdFormat
{
    public const PATTERN = '/^[A-Za-z0-9]{8,12}$/';

    public static function isValid(string $publicId): bool
    {
        return (bool) preg_match(self::PATTERN, $publicId);
    }
}
