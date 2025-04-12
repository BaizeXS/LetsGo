@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Profile Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row items-center">
            <div class="w-32 h-32 flex-shrink-0 mb-4 md:mb-0">
                <img src="{{ $user['avatar'] }}" alt="{{ $user['name'] }}" class="w-full h-full rounded-full object-cover">
            </div>
            <div class="md:ml-8 text-center md:text-left flex-grow">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">{{ $user['name'] }}</h1>
                        <p class="text-gray-600 mt-1">{{ $user['bio'] }}</p>
                        
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
                        @if(isset($user['tags']) && count($user['tags']) > 0)
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
                            <i class="fas fa-edit mr-2"></i>编辑资料
                        </a>
                        @else
                        <form action="{{ route('users.follow', $user['id']) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                <i class="fas fa-user-plus mr-2"></i>关注
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                
                <div class="flex justify-center md:justify-start space-x-8 mt-6">
                    <a href="{{ $isOwner ? route('user.profile') : route('users.profile', $user['name']) }}" class="text-center">
                        <span class="block text-xl font-bold">{{ $user['posts_count'] }}</span>
                        <span class="text-gray-500">游记</span>
                    </a>
                    <a href="{{ $isOwner ? route('user.followers') : route('users.followers', $user['name']) }}" class="text-center">
                        <span class="block text-xl font-bold">{{ $user['followers_count'] }}</span>
                        <span class="text-gray-500">粉丝</span>
                    </a>
                    <a href="{{ $isOwner ? route('user.following') : route('users.following', $user['name']) }}" class="text-center">
                        <span class="block text-xl font-bold">{{ $user['following_count'] }}</span>
                        <span class="text-gray-500">关注</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Navigation -->
    <div class="mb-6">
        <ul class="flex border-b">
            <li class="mr-1">
                <a href="{{ $isOwner ? route('user.profile') : route('users.profile', $user['name']) }}" class="bg-white inline-block py-2 px-4 text-blue-500 font-semibold border-b-2 border-blue-500">我的游记</a>
            </li>
            <li class="mr-1">
                <a href="{{ $isOwner ? route('user.favorites') : '#' }}" class="bg-white inline-block py-2 px-4 text-gray-500 hover:text-blue-500 font-semibold">我的收藏</a>
            </li>
        </ul>
    </div>

    <!-- User Posts -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($posts as $post)
        <div class="bg-white rounded-lg shadow-md overflow-hidden relative">
            @if(isset($post['pinned']) && $post['pinned'])
            <div class="absolute top-2 right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                <i class="fas fa-thumbtack mr-1"></i>置顶
            </div>
            @endif
            
            <a href="{{ route('posts.show', $post['id']) }}">
                <img src="{{ $post['cover_image'] }}" alt="{{ $post['title'] }}" class="w-full h-48 object-cover">
            </a>
            <div class="p-4">
                <h3 class="text-lg font-semibold mb-2">
                    <a href="{{ route('posts.show', $post['id']) }}" class="hover:text-blue-500">{{ $post['title'] }}</a>
                </h3>
                <p class="text-gray-500 text-sm mb-3">{{ $post['duration'] }}</p>
                <div class="flex items-center text-gray-500 text-sm">
                    <div class="flex items-center mr-4">
                        <i class="fas fa-eye mr-1"></i>
                        <span>{{ $post['views'] }}</span>
                    </div>
                    <div class="flex items-center mr-4">
                        <i class="fas fa-heart mr-1"></i>
                        <span>{{ $post['likes'] }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-comment mr-1"></i>
                        <span>{{ $post['comments'] }}</span>
                    </div>
                </div>
                
                @if($isOwner)
                <div class="mt-3 pt-3 border-t border-gray-100 flex justify-end">
                    <form action="{{ route('posts.pin', $post['id']) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-blue-500">
                            @if(isset($post['pinned']) && $post['pinned'])
                            <i class="fas fa-thumbtack mr-1"></i>取消置顶
                            @else
                            <i class="fas fa-thumbtack mr-1"></i>置顶
                            @endif
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Empty State -->
    @if(count($posts) == 0)
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <i class="fas fa-book-open text-5xl text-gray-300 mb-3"></i>
        <h3 class="text-xl font-medium text-gray-600 mb-2">还没有创建游记</h3>
        <p class="text-gray-500 mb-4">分享你的旅行故事，记录美好回忆</p>
        <a href="{{ route('posts.create') }}" class="inline-block px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
            <i class="fas fa-plus mr-2"></i>创建游记
        </a>
    </div>
    @endif
</div>
@endsection 