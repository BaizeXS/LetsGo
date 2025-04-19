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
     * Display login form
     */
    public function loginForm(Request $request)
    {
        return view('auth.login', [
            'redirect' => $request->query('redirect')
        ]);
    }
    
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Check if running in no-database mode
        if (!config('app.use_database', false)) {
            // Mock login process
            session(['mock_user' => [
                'id' => 1,
                'name' => $request->email,
                'email' => $request->email,
                'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg',
                'bio' => 'Passionate about travel, with footprints in over 30 countries and regions.',
                'location' => 'Hong Kong',
                'education' => 'Hong Kong University',
                'tags' => ['ENTP', 'Travel Blogger', 'Photography'],
                'posts_count' => 24,
                'followers_count' => 1280,
                'following_count' => 325,
                'is_mock' => true
            ]]);
            
            // Check if there's a redirect parameter
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
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->except('password'));
    }
    
    /**
     * Display registration form
     */
    public function registerForm(Request $request)
    {
        return view('auth.register', [
            'redirect' => $request->query('redirect')
        ]);
    }
    
    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        // Check if running in no-database mode
        if (!config('app.use_database', false)) {
            // Mock user registration - redirect to login page
            return redirect()->route('login')
                ->with('success', 'Registration successful! Please login with your account and password.')
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
        
        // Don't automatically log in the user, redirect to login page instead
        return redirect()->route('login')
            ->with('success', 'Registration successful! Please login with your account and password.')
            ->with('email', $request->email);
    }
    
    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        // Clear mock user session
        if (session()->has('mock_user')) {
            session()->forget('mock_user');
        }
        
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home');
    }
} 