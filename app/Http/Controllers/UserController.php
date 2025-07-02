<?php

namespace App\Http\Controllers;

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
                if (!empty($filter['id'] && !empty($filter['value']))) {
                    $query->whereIn($filter['id'], $filter['value']);
                }
            }
        });

        $query->when($request->query('sort'), function (Builder $query, ?string $sorting) {
            $sorting = json_decode($sorting, true);
            foreach ($sorting as $sort) {
                if (!empty($sort['id'] &&  !empty($sort['desc']))) {
                    $query->orderBy($sort['id'], $sort['desc'] ? 'desc' : 'asc');
                }
            }
        });

        $result = $query->paginate(self::PER_PAGE, ['*'], 'page', $request->query('page'))
            ->withQueryString()
            ->through(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status
            ]);


        return Inertia::render('Users', [
            'users' => $result,
            'search' => $request->query('search'),
            'filters' => json_decode($request->query('filters'), true),
            'sort' => json_decode($request->query('sort'), true)
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
