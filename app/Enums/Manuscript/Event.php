<?php

namespace App\Enums\Manuscript;

enum Event: string
{
    case STATUS_UPDATED = 'status_updated';

    public static function values()
    {
        return array_column(self::cases(), 'value');
    }
}
