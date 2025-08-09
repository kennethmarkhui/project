<?php

namespace App\Providers;

use App\Enums\RoleType;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::after(function ($user, $ability) {
            return $user->hasRole(RoleType::SUPER_ADMIN);
        });
        User::observe(UserObserver::class);
        JsonResource::withoutWrapping();
    }
}
