<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyUserRequest;
use App\Http\Requests\RestoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    protected const PER_PAGE = 10;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchableColumns = ['name', 'email'];

        $query = User::query();

        $query->when($request->query('search'), function (Builder $query, ?string $search) use ($searchableColumns) {
            $query->where(function ($query) use ($searchableColumns, $search) {
                foreach ($searchableColumns as $column) {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
            });
        });

        $query->when($request->query('filters'), function (Builder $query, ?string $filters) {
            $filters = json_decode($filters, true);
            foreach ($filters as $filter) {
                if (isset($filter['id']) && isset($filter['value'])) {
                    $query->whereIn($filter['id'], $filter['value']);
                }
            }
        });

        $query->when($request->query('sort'), function (Builder $query, ?string $sorting) {
            $sorting = json_decode($sorting, true);
            foreach ($sorting as $sort) {
                if (isset($sort['id']) &&  isset($sort['desc'])) {
                    $query->orderBy($sort['id'], $sort['desc'] ? 'desc' : 'asc');
                }
            }
        });

        $query->when($request->query('deleted'), function (Builder $query, ?string $deleted) {
            if ($deleted === 'only') {
                $query->onlyTrashed();
            } elseif ($deleted === 'with') {
                $query->withTrashed();
            }
        });

        $result = $query->paginate(self::PER_PAGE, ['*'], 'page', $request->query('page'))
            ->withQueryString();

        $filters =  array_merge(
            json_decode($request->query('filters'), true) ?? [],
            $request->filled('deleted') ? [['id' => 'deleted_at', 'value' => [$request->query('deleted')]]] : []
        ) ?: null;

        return Inertia::render('users/Index', [
            'users' => UserResource::collection($result),
            'search' => $request->query('search'),
            'filters' => $filters,
            'sort' => json_decode($request->query('sort'), true),
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
        return Inertia::render('users/Edit', [
            'user' => new UserResource($user)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request)
    {
        if ($request->isMultiple()) {
            $users = User::query()->whereIn('id', $request->ids());
            $users->update($request->validated());
        } else {
            $user = User::query()->findOrFail($request->route('id'));
            $user->fill($request->validated());

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();
        }

        return to_route('users');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyUserRequest $request)
    {
        $user = $request->user();
        $ids = $request->ids();

        if (in_array($user->id, $ids)) {
            abort(403);
        }

        $trashedCount = User::query()->onlyTrashed()->whereIn('id', $ids)->count();

        if ($trashedCount === count($ids)) {
            User::query()->onlyTrashed()->whereIn('id', $ids)->forceDelete();
        } else {
            User::whereIn('id', $ids)->whereNull('deleted_at')->delete();
        }

        return to_route('users');
    }

    public function restore(RestoreUserRequest $request)
    {
        $ids = $request->ids();

        $trashedCount = User::query()->onlyTrashed()->whereIn('id', $ids)->count();

        if ($trashedCount === count($ids)) {
            User::query()->onlyTrashed()->whereIn('id', $ids)->restore();
        }

        return to_route('users');
    }
}
