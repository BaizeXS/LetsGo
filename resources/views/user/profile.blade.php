@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- 个人信息区域 -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
        <div class="p-6 flex flex-col md:flex-row items-center md:items-start">
            <!-- 头像 -->
            <div class="w-24 h-24 md:w-32 md:h-32 rounded-full overflow-hidden bg-gray-200 flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                <img src="{{ $user['avatar'] ?? asset('images/default-avatar.jpg') }}" alt="{{ $user['name'] }}" class="w-full h-full object-cover">
            </div>
            
            <!-- 用户信息 -->
            <div class="text-center md:text-left flex-grow">
                <h1 class="text-2xl font-bold">{{ $user['name'] }}</h1>
                <p class="text-gray-600 mt-1">{{ $user['bio'] ?? '这个人很懒，还没有填写个人简介' }}</p>
                
                <!-- 用户统计 -->
                <div class="flex justify-center md:justify-start space-x-6 mt-4">
                    <div class="text-center">
                        <span class="block text-xl font-bold">{{ $user['posts_count'] ?? 0 }}</span>
                        <span class="text-gray-500 text-sm">笔记</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-xl font-bold">{{ $user['followers_count'] ?? 0 }}</span>
                        <span class="text-gray-500 text-sm">粉丝</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-xl font-bold">{{ $user['following_count'] ?? 0 }}</span>
                        <span class="text-gray-500 text-sm">关注</span>
                    </div>
                </div>
            </div>
            
            <!-- 操作按钮 -->
            @if($isOwner)
            <div class="mt-4 md:mt-0 md:ml-4">
                <a href="{{ route('user.edit') }}" class="inline-block px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    <i class="fas fa-edit mr-2"></i>编辑资料
                </a>
            </div>
            @else
            <div class="mt-4 md:mt-0 md:ml-4">
                <form action="{{ route('users.follow', $user['id']) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                        <i class="fas fa-user-plus mr-2"></i>关注
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- 内容切换 -->
    <div class="mb-6">
        <div class="flex border-b">
            <button id="posts-tab" class="flex-1 py-3 font-medium text-center border-b-2 border-blue-500 text-blue-500">我的笔记</button>
            <button id="favorites-tab" class="flex-1 py-3 font-medium text-center text-gray-500 hover:text-gray-700">我的收藏</button>
        </div>
    </div>

    <!-- 笔记内容区域 -->
    <div id="posts-content" class="block">
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
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 bg-white rounded-lg shadow-sm">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">还没有发布笔记</h3>
            <p class="mt-1 text-sm text-gray-500">开始记录你的旅行故事吧</p>
            <div class="mt-6">
                <a href="{{ route('posts.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none">
                    <i class="fas fa-plus mr-2"></i>
                    发布笔记
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- 收藏内容区域 -->
    <div id="favorites-content" class="hidden">
        @if(count($favorites ?? []) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($favorites as $post)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <a href="{{ route('posts.show', $post['id']) }}" class="block">
                    <img src="{{ $post['cover_image'] ?? asset('images/default-cover.jpg') }}" alt="{{ $post['title'] }}" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2">{{ $post['title'] }}</h3>
                        <div class="flex items-center mb-3">
                            <img src="{{ $post['author']['avatar'] ?? asset('images/default-avatar.jpg') }}" alt="{{ $post['author']['name'] }}" class="w-6 h-6 rounded-full mr-2">
                            <span class="text-sm text-gray-700">{{ $post['author']['name'] }}</span>
                        </div>
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
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 bg-white rounded-lg shadow-sm">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">还没有收藏内容</h3>
            <p class="mt-1 text-sm text-gray-500">浏览并收藏你喜欢的笔记</p>
            <div class="mt-6">
                <a href="{{ route('posts.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none">
                    <i class="fas fa-search mr-2"></i>
                    浏览笔记
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const postsTab = document.getElementById('posts-tab');
        const favoritesTab = document.getElementById('favorites-tab');
        const postsContent = document.getElementById('posts-content');
        const favoritesContent = document.getElementById('favorites-content');
        
        // 切换到我的笔记
        postsTab.addEventListener('click', function() {
            postsTab.classList.add('border-b-2', 'border-blue-500', 'text-blue-500');
            postsTab.classList.remove('text-gray-500');
            
            favoritesTab.classList.remove('border-b-2', 'border-blue-500', 'text-blue-500');
            favoritesTab.classList.add('text-gray-500');
            
            postsContent.classList.remove('hidden');
            postsContent.classList.add('block');
            
            favoritesContent.classList.remove('block');
            favoritesContent.classList.add('hidden');
        });
        
        // 切换到我的收藏
        favoritesTab.addEventListener('click', function() {
            favoritesTab.classList.add('border-b-2', 'border-blue-500', 'text-blue-500');
            favoritesTab.classList.remove('text-gray-500');
            
            postsTab.classList.remove('border-b-2', 'border-blue-500', 'text-blue-500');
            postsTab.classList.add('text-gray-500');
            
            favoritesContent.classList.remove('hidden');
            favoritesContent.classList.add('block');
            
            postsContent.classList.remove('block');
            postsContent.classList.add('hidden');
        });
    });
</script>
@endpush
@endsection 