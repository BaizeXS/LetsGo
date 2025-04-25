<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMockUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if there is a mock user in the session
        if (!config('app.use_database', false) && session()->has('mock_user') && !Auth::check()) {
            // Set view shared variables, so views can access the mock user
            view()->share('mockUser', session('mock_user'));
            view()->share('authUser', session('mock_user'));
            view()->share('isAuthenticated', true);
        }

        return $next($request);
    }
} 