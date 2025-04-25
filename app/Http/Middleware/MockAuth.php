<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MockAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated (standard Laravel auth or mock auth)
        if (Auth::check() || session()->has('mock_user')) {
            // Set mock_user to view shared variables
            if (session()->has('mock_user') && !Auth::check()) {
                view()->share('authUser', session('mock_user'));
                view()->share('isAuthenticated', true);
            }
            
            return $next($request);
        }
        
        // If not an AJAX request, redirect to login page
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        return redirect()->guest(route('login'));
    }
} 