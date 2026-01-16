<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Auth\StoredProcedureUserProvider;

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
        Auth::provider('stored_procedure', function ($app, array $config) {
            return new StoredProcedureUserProvider(
                $app['hash'],
                $config['model'] ?? \App\Models\User::class,
            );
        });
    }
}
