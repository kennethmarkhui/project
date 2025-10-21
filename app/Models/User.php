<?php

namespace App\Models;

use App\Observers\UserObserver;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Models\HasSuperAdmin;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles, HasSuperAdmin, TwoFactorAuthenticatable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('user')
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['password'])
            ->dontSubmitEmptyLogs();
    }

    /**
     * Scope a query to only include filtered users.
     */
    #[Scope]
    protected function filterBy(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null,  function (Builder $query, string $text) {
            $this->applySearchFilter($query, $text, ['name', 'email']);
        })->when($filters['filters'] ?? null, function (Builder $query, string $filters) {
            $this->applyColumnFilters($query, $filters);
        })->when($filters['deleted'] ?? null, function (Builder $query, string $showFilter) {
            $this->applySoftDeleteFilter($query, $showFilter);
        });
    }

    /**
     * Scope a query to only include sorted users.
     */
    #[Scope]
    protected function sortBy(Builder $query, array $sorting): void
    {
        $query->when($sorting['sort'] ?? null, function (Builder $query, string $sortingJson) {
            $this->applySorting($query, $sortingJson);
        });
    }

    /**
     * Apply search filter to the query.
     */
    protected function applySearchFilter(Builder $query, string $text, array $searchableColumns = []): void
    {
        $query->where(function ($query) use ($searchableColumns, $text) {
            $table = $query->getModel()->getTable();
            foreach ($searchableColumns as $column) {
                $query->orWhere("{$table}.{$column}", 'like', "%{$text}%");
            }
        });
    }

    /**
     * Apply column-specific filters.
     */
    protected function applyColumnFilters(Builder $query, string $filtersJson): void
    {
        $filters = json_decode($filtersJson, true) ?: [];

        foreach ($filters as $filter) {
            if (!isset($filter['id'], $filter['value'])) continue;

            $table = $query->getModel()->getTable();
            match ($filter['id']) {
                'role' => $this->applyRoleFilter($query, $filter['value']),
                default => $query->whereIn("{$table}.{$filter['id']}", $filter['value'])
            };
        }
    }

    /**
     * Apply role filter.
     */
    protected function applyRoleFilter(Builder $query, mixed $roles): void
    {
        $query->whereHas('roles', function ($query) use ($roles) {
            $query->whereIn('roles.name', $roles);
        });
    }

    /**
     * Apply soft delete status filter.
     */
    protected function applySoftDeleteFilter(Builder $query, string $showFilter): void
    {
        match ($showFilter) {
            'only' => $query->onlyTrashed(),
            'with' => $query->withTrashed(),
            default => null
        };
    }

    /**
     * Apply sorting to the query.
     */
    protected function applySorting(Builder $query, string $sortingJson): void
    {
        $sorts = json_decode($sortingJson, true) ?: [];

        foreach ($sorts as $sort) {
            if (!isset($sort['id'], $sort['desc'])) continue;

            $table = $query->getModel()->getTable();
            match ($sort['id']) {
                'role' => $this->applyRoleSorting($query, $sort['desc']),
                default => $query->orderBy("{$table}.{$sort['id']}", $sort['desc'] ? 'desc' : 'asc')
            };
        }
    }

    /**
     * Apply role-based sorting.
     */
    protected function applyRoleSorting(Builder $query, bool $descending): void
    {
        $query->leftJoin('model_has_roles', function ($join) use ($query) {
            $join->on('users.id', '=', 'model_has_roles.model_id')
                ->where('model_has_roles.model_type',  $query->getModel()::class);
        })
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->orderBy('roles.name', $descending ? 'desc' : 'asc')
            ->select('users.*');
    }

    /**
     * Get the invitations made by the user.
     */
    public function invitees(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
