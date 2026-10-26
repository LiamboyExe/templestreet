<?php

namespace App\Enums;

enum MemberStatus: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';
    case Pending = 'Pending';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $status): string => $status->value, self::cases());
    }
}
