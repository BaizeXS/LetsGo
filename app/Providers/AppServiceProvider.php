<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\MockAuth;

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
        // Register middleware alias
        Route::aliasMiddleware('mock.auth', MockAuth::class);
        
        // Check mock user
        if (!config('app.use_database', false)) {
            view()->composer('*', function ($view) {
                if (session()->has('mock_user')) {
                    $view->with('authUser', session('mock_user'));
                    $view->with('isAuthenticated', true);
                } else {
                    $view->with('isAuthenticated', false);
                }
            });
        }
    }
}
