<?php

namespace App\Providers;

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
        // 检查模拟用户
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
