@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-6">
        <h2 class="text-center text-2xl font-bold text-gray-900 mb-6">Log In to LetsGO</h2>
        
        @if (session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-md p-4">
                {{ session('success') }}
            </div>
        @endif
        
        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-md p-4">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            @if(isset($redirect))
                <input type="hidden" name="redirect" value="{{ $redirect }}">
            @endif
            
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
                <input id="email" type="email" name="email" value="{{ session('email') ?? old('email') }}" required autofocus
                    class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                <input id="password" type="password" name="password" required
                    class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <div class="mb-6 flex items-center">
                <input id="remember" type="checkbox" name="remember" class="h-4 w-4 text-red-500 border-gray-300 rounded focus:ring-red-500">
                <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
            </div>
            
            <div class="flex flex-col space-y-4">
                <button type="submit" class="w-full py-2 px-4 bg-red-500 text-white rounded-full hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Log In
                </button>
                
                <div class="text-center text-sm text-gray-600">
                    <p>Don't have an account? <a href="{{ route('register') }}" class="text-red-500 hover:text-red-700">Register now</a></p>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection 