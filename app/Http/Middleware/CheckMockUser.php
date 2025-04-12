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
        // 检查会话中是否有模拟用户
        if (!config('app.use_database', false) && session()->has('mock_user') && !Auth::check()) {
            // 设置视图共享变量，使视图可以访问模拟用户
            view()->share('mockUser', session('mock_user'));
        }

        return $next($request);
    }
} 