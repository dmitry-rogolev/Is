<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        config('is.models.role')::macro('users', function () {
            return $this->roleables(config('is.models.user'));
        });
    }
}
