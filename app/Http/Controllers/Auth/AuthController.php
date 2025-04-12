<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * 显示登录表单
     */
    public function loginForm(Request $request)
    {
        return view('auth.login', [
            'redirect' => $request->query('redirect')
        ]);
    }
    
    /**
     * 处理登录请求
     */
    public function login(Request $request)
    {
        // 检查是否在不使用数据库模式下运行
        if (!config('app.use_database', false)) {
            // 模拟登录流程
            session(['mock_user' => [
                'id' => 1,
                'name' => $request->email,
                'email' => $request->email,
                'is_mock' => true
            ]]);
            
            // 检查是否有重定向参数
            if ($request->has('redirect')) {
                return redirect($request->redirect);
            }
            
            return redirect()->intended(route('home'));
        }
        
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            // Check if there's a redirect parameter
            if ($request->has('redirect')) {
                return redirect($request->redirect);
            }
            
            return redirect()->intended(route('home'));
        }
        
        return back()->withErrors([
            'email' => '提供的凭据不匹配我们的记录。',
        ])->withInput($request->except('password'));
    }
    
    /**
     * 显示注册表单
     */
    public function registerForm(Request $request)
    {
        return view('auth.register', [
            'redirect' => $request->query('redirect')
        ]);
    }
    
    /**
     * 处理注册请求
     */
    public function register(Request $request)
    {
        // 检查是否在不使用数据库模式下运行
        if (!config('app.use_database', false)) {
            // 模拟用户注册 - 直接跳转到登录页面
            return redirect()->route('login')
                ->with('success', '注册成功！请使用您的账号和密码登录。')
                ->with('email', $request->email);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        // 不自动登录用户，而是跳转到登录页面
        return redirect()->route('login')
            ->with('success', '注册成功！请使用您的账号和密码登录。')
            ->with('email', $request->email);
    }
    
    /**
     * 处理登出请求
     */
    public function logout(Request $request)
    {
        // 清除模拟用户会话
        if (session()->has('mock_user')) {
            session()->forget('mock_user');
        }
        
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home');
    }
} 