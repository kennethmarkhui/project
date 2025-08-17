<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\Authorizable\RoleResource as AuthorizableRoleResource;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::query()->withCount(['users', 'permissions'])->with('permissions')->get();
        $permissions = Permission::all();

        return Inertia::render('roles/Index', [
            'roles' => $roles->toResourceCollection(AuthorizableRoleResource::class),
            'permissions' => $permissions->toResourceCollection(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Role::class);

        $permissions = Permission::all();

        return Inertia::render('roles/Create', [
            'permissions' => $permissions->toResourceCollection(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        Gate::authorize('create', Role::class);

        $validated = $request->validated();
        $role = Role::query()->create(Arr::except($validated, 'permissions'));

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return to_route('roles')->with('success', 'Role has been created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        Gate::authorize('view', $role);

        return Inertia::render('roles/Show', [
            'role' => $role->loadCount(['users', 'permissions'])->load('permissions')->toResource(AuthorizableRoleResource::class),
            'permissions' => Permission::all()->toResourceCollection(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        Gate::authorize('update', $role);

        return Inertia::render('roles/Edit', [
            'role' => $role->loadCount(['users', 'permissions'])->load('permissions')->toResource(AuthorizableRoleResource::class),
            'permissions' => Permission::all()->toResourceCollection(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        Gate::authorize('update', $role);

        $validated = $request->validated();
        $role->update(Arr::except($validated, 'permissions'));
        $role->syncPermissions($validated['permissions']);

        return back()->with('success', 'Role has been updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Role $role)
    {
        Gate::authorize('delete', $role);

        $role->delete();

        return $request->input('from') === 'roles' ? back()->with('success', 'Role has been deleted') : to_route('roles')->with('success', 'Role has been deleted');
    }
}
