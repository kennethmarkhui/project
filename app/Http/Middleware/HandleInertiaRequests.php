<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user() ? $request->user()->toResource() : null,
                'can' => $request->user() ? Permission::all()->pluck('name')
                    ->filter(fn($name) => str_contains($name, '.'))
                    ->groupBy(fn($name) => explode('.', $name)[0])
                    ->map(function ($permissions, $resource) use ($request) {
                        return $permissions
                            ->mapWithKeys(function ($fullPermission) use ($request) {
                                $action = explode('.', $fullPermission)[1];
                                return [$action => $request->user()->can($fullPermission)];
                            });
                    })
                    ->toArray() : [],
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
