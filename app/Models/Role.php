<?php

namespace App\Models;

use App\Traits\Models\HasSuperAdmin;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasSuperAdmin;
}
