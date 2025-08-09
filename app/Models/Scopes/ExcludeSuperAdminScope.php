<?php

namespace App\Models\Scopes;

use App\Enums\RoleType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class ExcludeSuperAdminScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!Auth::hasUser()) return;

        if (Auth::user()->hasRole(RoleType::SUPER_ADMIN)) return;

        if ($model instanceof Role) {
            $builder->whereNot('name', RoleType::SUPER_ADMIN->value);
        } elseif ($model instanceof User) {
            $builder->whereDoesntHave(
                'roles',
                fn($query) => $query->where('name', RoleType::SUPER_ADMIN->value)
            );
        }
    }

    /**
     * Extend the query builder with the needed functions.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withSuperAdmins', fn(Builder $builder) => $builder->withoutGlobalScope($this));
    }
}
