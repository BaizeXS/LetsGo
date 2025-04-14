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
        // 检查用户是否已认证（标准Laravel认证或模拟认证）
        if (Auth::check() || session()->has('mock_user')) {
            // 将mock_user设置到视图共享变量
            if (session()->has('mock_user') && !Auth::check()) {
                view()->share('authUser', session('mock_user'));
                view()->share('isAuthenticated', true);
            }
            
            return $next($request);
        }
        
        // 如果不是AJAX请求，重定向到登录页面
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        return redirect()->guest(route('login'));
    }
} 