<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\BulkDestroyUserRequest;
use App\Http\Requests\User\BulkRestoreUserRequest;
use App\Http\Requests\User\BulkUpdateUserRequest;
use App\Http\Requests\User\BulkForceDeleteUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\Authorizable\UserResource as AuthorizableUserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UserController extends Controller
{
    protected const PER_PAGE = 10;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $query = User::query()->with('roles');

        $query->filterBy($request->only('search', 'filters', 'deleted'))
            ->sortBy($request->only('sort'));

        $result = $query->paginate($request->query('per_page') ?? self::PER_PAGE, ['*'], 'page', $request->query('page'))
            ->withQueryString();

        $filters = array_merge(
            json_decode($request->query('filters'), true) ?? [],
            $request->filled('deleted') ? [['id' => 'deleted_at', 'value' => [$request->query('deleted')]]] : []
        ) ?: null;

        return Inertia::render('users/Index', [
            'users' => $result->toResourceCollection(AuthorizableUserResource::class),
            'search' => $request->query('search'),
            'filters' => $filters,
            'sort' => json_decode($request->query('sort'), true),
            'roles' => Role::all()->toResourceCollection()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        Gate::authorize('update', $user);

        return Inertia::render('users/Edit', [
            'user' => $user->load('roles')->toResource(AuthorizableUserResource::class),
            'roles' => Role::all()->pluck('name')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        Gate::authorize('update', $user);

        $validated = $request->validated();

        $user->fill(Arr::except($validated, 'role'));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if (array_key_exists('role', $validated)) {
            $user->syncRoles($validated['role']);
        }

        return to_route('users')->with('success', 'User has been updated');
    }

    public function bulkUpdate(BulkUpdateUserRequest $request)
    {
        $ids = $request->ids();

        $users = User::whereIn('id', $ids)->get();

        Gate::authorize('updateAny', [User::class, $users]);

        $validated = $request->validated();

        abort_if($users->count() !== count($ids), 422);

        $query = User::query()->whereIn('id', $ids);

        $query->update(Arr::except($validated, 'role'));

        if (array_key_exists('role', $validated)) {
            $query->each(function ($user) use ($validated) {
                $user->syncRoles($validated['role']);
            });
        }

        return back()->with('success', 'Users has been updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return $request->input('from') === 'users'
            ? back()->with('success', 'User has been deleted')
            : to_route('users')->with('success', 'User has been deleted');
    }

    public function bulkDestroy(BulkDestroyUserRequest $request)
    {
        $ids = $request->ids();

        $users = User::whereIn('id', $ids)->get();

        abort_if($users->count() !== count($ids), 422);

        Gate::authorize('deleteAny', [User::class, $users]);

        User::whereIn('id', $ids)->delete();

        return back()->with('success', 'Users has been deleted');
    }

    /**
     * Restore the specified soft-deleted resource.
     */
    public function restore(Request $request, User $user)
    {
        Gate::authorize('restore', $user);

        $user->restore();

        return $request->input('from') === 'users'
            ? back()->with('success', 'User has been restored')
            : to_route('users')->with('success', 'User has been restored');
    }

    public function bulkRestore(BulkRestoreUserRequest $request)
    {
        $ids = $request->ids();

        $users = User::onlyTrashed()->whereIn('id', $ids)->get();

        abort_if($users->count() !== count($ids), 422);

        Gate::authorize('restoreAny', [User::class, $users]);

        User::onlyTrashed()->whereIn('id', $ids)->restore();

        return back()->with('success', 'Users has been restored');
    }

    /**
     * Permanently delete the specified resource.
     */
    public function forceDelete(Request $request, User $user)
    {
        Gate::authorize('forceDelete', $user);

        $user->forceDelete();

        return $request->input('from') === 'users'
            ? back()->with('success', 'User has been permanently deleted')
            : to_route('users')->with('success', 'User has been permanently deleted');
    }

    public function bulkForceDelete(BulkForceDeleteUserRequest $request)
    {
        $ids = $request->ids();

        $users = User::onlyTrashed()->whereIn('id', $ids)->get();

        abort_if($users->count() !== count($ids), 422);

        Gate::authorize('forceDeleteAny', [User::class, $users]);

        User::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return back()->with('success', 'Users has been permanently deleted');
    }
}
