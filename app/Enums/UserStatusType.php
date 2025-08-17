<?php

namespace App\Enums;

enum UserStatusType: string
{
    case APPROVED = 'approved';
    case DENIED = 'denied';
    case PENDING = 'pending';

    /**
     * Get all permission values
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
