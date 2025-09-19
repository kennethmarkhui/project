<?php

namespace App\Enums;

enum RoleType: string
{
    case SUPER_ADMIN = 'super admin';
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case USER = 'user';

    /**
     * Get all permission values
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all permissions for a given role
     * @return PermissionType[]
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SUPER_ADMIN => [],
            self::ADMIN => [
                PermissionType::USER_READ,
                PermissionType::USER_UPDATE,
                PermissionType::USER_DELETE,
                PermissionType::USER_FORCE_DELETE,
                PermissionType::USER_RESTORE,
                PermissionType::USER_INVITE,
                PermissionType::ROLE_CREATE,
                PermissionType::ROLE_READ,
                PermissionType::ROLE_UPDATE,
                PermissionType::ROLE_DELETE,
                PermissionType::PERMISSION_READ,
                PermissionType::ACTIVITY_LOG_READ,
            ],
            self::EDITOR => [
                PermissionType::USER_READ,
                PermissionType::USER_UPDATE,
                PermissionType::USER_DELETE,
            ],
            self::USER => []
        };
    }
}
