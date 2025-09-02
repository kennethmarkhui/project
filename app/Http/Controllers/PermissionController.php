<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Gate::authorize('viewAny', Permission::class);

        $permissions = Permission::query()->withCount(['roles'])->with('roles')->get();
        $roles = Role::all();

        return Inertia::render('permissions/Index', [
            'permissions' => $permissions->toResourceCollection(),
            'roles' => $roles->toResourceCollection()
        ]);
    }
}
