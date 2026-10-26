<?php

namespace App\Enums;

enum NfcCardStatus: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';
    case Lost = 'Lost';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $status): string => $status->value, self::cases());
    }
}
