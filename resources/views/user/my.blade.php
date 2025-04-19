@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Profile information area -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
        <div class="p-6 flex flex-col md:flex-row items-center md:items-start">
            <!-- Avatar -->
            <div class="w-24 h-24 md:w-32 md:h-32 rounded-full overflow-hidden bg-gray-200 flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                <img src="{{ $user['avatar'] ?? asset('images/default-avatar.jpg') }}" alt="{{ $user['name'] }}" class="w-full h-full object-cover">
            </div>
            
            <!-- User information -->
            <div class="text-center md:text-left flex-grow">
                <h1 class="text-2xl font-bold">{{ $user['name'] }}</h1>
                <p class="text-gray-600 mt-1">{{ $user['bio'] ?? 'This person is lazy and hasn\'t filled in their bio yet' }}</p>
                
                <!-- User statistics -->
                <div class="flex justify-center md:justify-start space-x-6 mt-4">
                    <div class="text-center">
                        <span class="block text-xl font-bold">{{ $user['posts_count'] ?? 0 }}</span>
                        <span class="text-gray-500 text-sm">Posts</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-xl font-bold">{{ $user['followers_count'] ?? 0 }}</span>
                        <span class="text-gray-500 text-sm">Followers</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-xl font-bold">{{ $user['following_count'] ?? 0 }}</span>
                        <span class="text-gray-500 text-sm">Following</span>
                    </div>
                </div>
            </div>
            
            <!-- Action buttons -->
            <div class="mt-4 md:mt-0 md:ml-4">
                <a href="{{ route('user.edit') }}" class="inline-block px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Title area -->
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-xl font-bold">My Posts</h2>
        <a href="{{ route('posts.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none">
            <i class="fas fa-plus mr-2"></i>
            Create Post
        </a>
    </div>

    <!-- Posts content area -->
    <div id="posts-content">
        @if(count($posts ?? []) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($posts as $post)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <a href="{{ route('posts.show', $post['id']) }}" class="block">
                    <img src="{{ $post['cover_image'] ?? asset('images/default-cover.jpg') }}" alt="{{ $post['title'] }}" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2">{{ $post['title'] }}</h3>
                        <p class="text-gray-500 text-sm mb-3 line-clamp-2">{{ $post['content'] }}</p>
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>{{ $post['created_at'] }}</span>
                            <div class="flex space-x-3">
                                <span><i class="fas fa-eye mr-1"></i>{{ $post['views'] ?? 0 }}</span>
                                <span><i class="fas fa-heart mr-1"></i>{{ $post['likes'] ?? 0 }}</span>
                                <span><i class="fas fa-comment mr-1"></i>{{ $post['comments'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </a>
                <div class="px-4 pb-4 flex justify-end space-x-2">
                    <a href="{{ route('posts.edit', $post['id']) }}" class="text-sm px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                    <form action="{{ route('posts.destroy', $post['id']) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this post?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 bg-white rounded-lg shadow-sm">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No posts published yet</h3>
            <p class="mt-1 text-sm text-gray-500">Start recording your travel stories</p>
            <div class="mt-6">
                <a href="{{ route('posts.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none">
                    <i class="fas fa-plus mr-2"></i>
                    Create Post
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection 