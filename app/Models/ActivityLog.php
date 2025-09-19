<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    /**
     * Scope a query to only include filtered activity.
     */
    #[Scope]
    protected function filterBy(Builder $query, array $filters): void
    {
        $query->when($filters['filters'] ?? null, function (Builder $query, string $filters) {
            $this->applyColumnFilters($query, $filters);
        });
    }

    /**
     * Scope a query to only include sorted activity.
     */
    #[Scope]
    protected function sortBy(Builder $query, array $sorting): void
    {
        $query->when($sorting['sort'] ?? null, function (Builder $query, string $sortingJson) {
            $this->applySorting($query, $sortingJson);
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
            if (isset($filter['value']['start'], $filter['value']['end'])) {
                $query->whereBetween("{$table}.{$filter['id']}", [
                    Carbon::parse($filter['value']['start'])->startOfDay(),
                    Carbon::parse($filter['value']['end'])->endOfDay()
                ]);
            } else {
                $query->whereIn("{$table}.{$filter['id']}", $filter['value']);
            }
        }
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
                'causer' => $this->applyCauserSorting($query, $sort['desc']),
                default => $query->orderBy("{$table}.{$sort['id']}", $sort['desc'] ? 'desc' : 'asc')
            };
        }
    }

    /**
     * Apply causer sorting.
     */
    protected function applyCauserSorting(Builder $query, bool $descending): void
    {
        $query->orderBy(
            User::select('email')
                ->whereColumn('id', 'activity_log.causer_id')
                ->where('activity_log.causer_type', (new User)->getMorphClass())
                ->limit(1),
            $descending ? 'desc' : 'asc'
        );
    }
}
