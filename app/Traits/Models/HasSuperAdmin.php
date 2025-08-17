<?php

namespace App\Traits\Models;

use App\Models\Scopes\ExcludeSuperAdminScope;

trait HasSuperAdmin
{
    public static function bootHasSuperAdmin()
    {
        static::addGlobalScope(new ExcludeSuperAdminScope);
    }
}
