<?php

namespace App\Enums;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

enum PermissionType: string
{
    case USER_READ = 'user.read';
    case USER_UPDATE = 'user.update';
    case USER_DELETE = 'user.delete';
    case USER_FORCE_DELETE = 'user.force_delete';
    case USER_RESTORE = 'user.restore';

    case ROLE_CREATE = 'role.create';
    case ROLE_READ = 'role.read';
    case ROLE_UPDATE = 'role.update';
    case ROLE_DELETE = 'role.delete';

    case PERMISSION_READ = 'permission.read';

    /**
     * Get all permission values
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all permissions for a given model class
     * @return PermissionType[]
     */
    public static function forModel(string $model): array
    {
        return match ($model) {
            User::class => [
                self::USER_READ,
                self::USER_UPDATE,
                self::USER_DELETE,
                self::USER_FORCE_DELETE,
                self::USER_RESTORE,
            ],
            Role::class => [
                self::ROLE_CREATE,
                self::ROLE_READ,
                self::ROLE_UPDATE,
                self::ROLE_DELETE,
            ],
            Permission::class => [
                self::PERMISSION_READ,
            ],
            default => [],
        };
    }

    /**
     * Get all permission values for a given model class
     * @return string[]
     */
    public static function forModelValues(string $model): array
    {
        return array_column(self::forModel($model), 'value');
    }
}
