@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Profile Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row items-center">
            <div class="w-32 h-32 flex-shrink-0 mb-4 md:mb-0">
                <img src="{{ $user['avatar'] ?? asset('images/default-avatar.jpg') }}" alt="{{ $user['name'] ?? 'User' }}" class="w-full h-full rounded-full object-cover">
            </div>
            <div class="md:ml-8 text-center md:text-left flex-grow">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">{{ $user['name'] ?? 'User' }}</h1>
                        <p class="text-gray-600 mt-1">{{ $user['bio'] ?? 'No bio available' }}</p>

                        <!-- Location and Education Info -->
                        @if(isset($user['location']) || isset($user['education']))
                        <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-gray-500">
                            @if(isset($user['location']))
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <span>{{ $user['location'] }}</span>
                            </div>
                            @endif
                            
                            @if(isset($user['education']))
                            <div class="flex items-center">
                                <i class="fas fa-graduation-cap mr-1"></i>
                                <span>{{ $user['education'] }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                        
                        <!-- User Tags -->
                        @if(isset($user['tags']) && is_array($user['tags']) && count($user['tags']) > 0)
                        <div class="flex flex-wrap gap-2 mt-3">
                            @foreach($user['tags'] as $tag)
                            <span class="inline-block bg-gray-100 px-2 py-1 text-xs rounded-full text-gray-600">{{ $tag }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="mt-4 md:mt-0">
                        @if($isOwner)
                        <a href="{{ route('user.edit') }}" class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                            <i class="fas fa-edit mr-2"></i>Edit Profile
                        </a>
                        @else
                        <form action="{{ route('users.follow', $user['id']) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                <i class="fas fa-user-plus mr-2"></i>Follow
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                
                <div class="flex justify-center md:justify-start space-x-8 mt-6">
                    <a href="{{ route('home') }}" class="text-center">
                        <span class="block text-xl font-bold">{{ $user['posts_count'] ?? 0 }}</span>
                        <span class="text-gray-500">Posts</span>
                    </a>
                    <a href="{{ $isOwner ? route('user.followers') : route('users.followers', $user['name'] ?? '') }}" class="text-center">
                        <span class="block text-xl font-bold">{{ $user['followers_count'] ?? 0 }}</span>
                        <span class="text-gray-500">Followers</span>
                    </a>
                    <a href="{{ $isOwner ? route('user.following') : route('users.following', $user['name'] ?? '') }}" class="text-center">
                        <span class="block text-xl font-bold">{{ $user['following_count'] ?? 0 }}</span>
                        <span class="text-gray-500">Following</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Navigation -->
    <div class="mb-6">
        <ul class="flex border-b">
            <li class="mr-1">
                <a href="{{ route('user.favorites') }}" class="bg-white inline-block py-2 px-4 text-blue-500 font-semibold border-b-2 border-blue-500">My Favorites</a>
            </li>
            <li class="mr-1">
                <a href="{{ route('user.my.posts') }}" class="bg-white inline-block py-2 px-4 text-gray-500 hover:text-blue-500 font-semibold">My Posts</a>
            </li>
        </ul>
    </div>

    <!-- Favorite Posts -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($favorites as $post)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <a href="{{ route('posts.show', $post['id']) }}">
                <img src="{{ $post['cover_image'] ?? asset('images/default-cover.jpg') }}" alt="{{ $post['title'] ?? 'Post' }}" class="w-full h-48 object-cover">
            </a>
            <div class="p-4">
                <h3 class="text-lg font-semibold mb-2">
                    <a href="{{ route('posts.show', $post['id']) }}" class="hover:text-blue-500">{{ $post['title'] ?? 'Untitled Post' }}</a>
                </h3>
                <div class="flex items-center mb-3">
                    <img src="{{ $post['author']['avatar'] ?? asset('images/default-avatar.jpg') }}" alt="{{ $post['author']['name'] ?? 'Author' }}" class="w-6 h-6 rounded-full mr-2">
                    <span class="text-sm text-gray-600">{{ $post['author']['name'] ?? 'Unknown Author' }}</span>
                </div>
                <p class="text-gray-500 text-sm mb-3">{{ $post['duration'] ?? '' }}</p>
                <div class="flex items-center text-gray-500 text-sm">
                    <div class="flex items-center mr-4">
                        <i class="fas fa-eye mr-1"></i>
                        <span>{{ $post['views'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center mr-4">
                        <i class="fas fa-heart mr-1"></i>
                        <span>{{ $post['likes'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-comment mr-1"></i>
                        <span>{{ $post['comments'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Empty State -->
    @if(count($favorites) == 0)
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <i class="fas fa-heart text-5xl text-gray-300 mb-3"></i>
        <h3 class="text-xl font-medium text-gray-600 mb-2">No favorites yet</h3>
        <p class="text-gray-500 mb-4">Save your favorite travel notes for easy access later</p>
        <a href="{{ route('posts.index') }}" class="inline-block px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
            <i class="fas fa-search mr-2"></i>Browse Posts
        </a>
    </div>
    @endif

    <!-- Pagination -->
    @if(count($favorites) > 0 && isset($favorites->links))
    <div class="mt-8">
        {{ $favorites->links() }}
    </div>
    @endif
</div>
@endsection 