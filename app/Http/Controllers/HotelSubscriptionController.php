<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HotelSubscriptionController extends Controller
{
    public function index()
    {
        return view('hotels.subscription', [
            'isAuthenticated' => session()->has('auth_user'),
            'authUser' => session('auth_user') ?? []
        ]);
    }
}
