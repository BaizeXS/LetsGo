@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-6">
        <h2 class="text-center text-2xl font-bold text-gray-900 mb-6">Create a LetsGO Account</h2>
        
        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-md p-4">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            @if(isset($redirect))
                <input type="hidden" name="redirect" value="{{ $redirect }}">
            @endif
            
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-medium mb-2">Full Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                <input id="password" type="password" name="password" required
                    class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-500">
                <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters long</p>
            </div>
            
            <div class="mb-6">
                <label for="password_confirmation" class="block text-gray-700 text-sm font-medium mb-2">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <div class="flex flex-col space-y-4">
                <button type="submit" class="w-full py-2 px-4 bg-red-500 text-white rounded-full hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Register
                </button>
                
                <div class="text-center text-sm text-gray-600">
                    <p>Already have an account? <a href="{{ route('login') }}" class="text-red-500 hover:text-red-700">Log in</a></p>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection 