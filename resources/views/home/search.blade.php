@extends('layouts.app')

@section('content')
    <!-- Category tabs -->
    <x-category-tabs :categories="$categories" :activeCategory="$activeCategory" />
    
    <!-- Search results header -->
    <div class="bg-white rounded-lg shadow-sm p-4 mt-4 mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Search results for: <span class="text-red-500">"{{ $query }}"</span></h1>
        <p class="text-gray-600 mt-1">Found {{ count($posts) }} results</p>
    </div>

    @if(count($posts) > 0)
        <!-- Card grid -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="posts-container">
            @foreach($posts as $post)
                <div class="post-card">
                    <x-travel-card :post="$post" />
                </div>
            @endforeach
        </div>
    @else
        <!-- No results -->
        <div class="bg-gray-50 rounded-lg p-8 text-center">
            <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">No results found</h2>
            <p class="text-gray-600 mb-4">Try different keywords or browse categories</p>
            <a href="{{ route('home') }}" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-6 rounded-full">
                Back to Home
            </a>
        </div>
    @endif
@endsection 