<?php

namespace App\Providers;

use App\Enums\RoleType;
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
        JsonResource::withoutWrapping();
    }
}
