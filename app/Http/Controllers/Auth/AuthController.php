<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use App\Models\User;
// use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * 显示登录表单
     */
    public function loginForm()
    {
        return view('auth.login');
    }
    
    /**
     * 处理登录请求
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('home'));
        }
        
        return back()->withErrors([
            'email' => '提供的凭据不匹配我们的记录。',
        ])->withInput($request->except('password'));
    }
    
    /**
     * 显示注册表单
     */
    public function registerForm()
    {
        return view('auth.register');
    }
    
    /**
     * 处理注册请求
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        /*
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        Auth::login($user);
        */
        
        return redirect()->route('home');
    }
    
    /**
     * 处理登出请求
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home');
    }
} 