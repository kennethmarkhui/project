<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Gate::authorize('viewAny', ActivityLog::class);

        $query = ActivityLog::query()->with('causer');

        $query->filterBy($request->only('filters'))
            ->sortBy($request->only('sort'));

        $result = $query
            ->paginate($request->query('per_page') ?? config('default.per_page'), ['*'], 'page', $request->query('page'))
            ->withQueryString();

        $events = ActivityLog::query()
            ->distinct()
            ->pluck('event')
            ->toArray();

        $log_names = ActivityLog::query()
            ->distinct()
            ->pluck('log_name')
            ->toArray();

        return Inertia::render('activity-logs/Index', [
            'activity_logs' => $result->toResourceCollection(),
            'filters' => json_decode($request->query('filters'), true),
            'sort' => json_decode($request->query('sort'), true),
            'events' => $events,
            'log_names' => $log_names,
        ]);
    }
}
