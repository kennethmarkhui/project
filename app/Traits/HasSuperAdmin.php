<?php

namespace App\Traits;

use App\Models\Scopes\ExcludeSuperAdminScope;

trait HasSuperAdmin
{
    public static function bootHasSuperAdmin()
    {
        static::addGlobalScope(new ExcludeSuperAdminScope);
    }
}
