<?php

namespace App\Enums;

enum MembershipType: string
{
    case Regular = 'Regular';
    case Premium = 'Premium';
    case Student = 'Student';
    case Senior = 'Senior';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $type): string => $type->value, self::cases());
    }
}
