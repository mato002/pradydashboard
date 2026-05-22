<?php

namespace App\Support\Billing;

final class CollectionNoteStatus
{
    public const OPEN = 'open';

    public const COMPLETED = 'completed';

    public const CANCELLED = 'cancelled';

    /** @return list<string> */
    public static function all(): array
    {
        return [self::OPEN, self::COMPLETED, self::CANCELLED];
    }
}
