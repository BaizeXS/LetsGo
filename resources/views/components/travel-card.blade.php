@props(['post'])

<div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
    <!-- 卡片封面图 -->
    <div class="relative">
        <img src="{{ $post['cover_image'] }}" alt="{{ $post['title'] }}" class="w-full h-48 object-cover">
        
        <!-- 收藏按钮 -->
        <button 
            class="absolute top-3 right-3 bg-white bg-opacity-70 hover:bg-opacity-100 rounded-full p-2 transition-all duration-200"
            onclick="toggleFavorite(this, {{ $post['id'] }})"
        >
            <i class="far fa-heart text-red-500" id="heart-{{ $post['id'] }}"></i>
        </button>
    </div>
    
    <!-- 卡片内容 -->
    <div class="p-4">
        <!-- 用户信息 -->
        <div class="flex items-center mb-3">
            <img src="{{ $post['user']['avatar'] }}" alt="{{ $post['user']['name'] }}" class="w-8 h-8 rounded-full mr-2">
            <span class="text-sm text-gray-700">{{ $post['user']['name'] }}</span>
        </div>
        
        <!-- 标题 -->
        <h3 class="font-bold text-gray-900 mb-2">{{ $post['title'] }}</h3>
        
        <!-- 标签信息 -->
        <div class="flex items-center text-xs text-gray-500 mb-3">
            <span class="mr-3">{{ $post['duration'] }}</span>
            @if(isset($post['cost']))
            <span>{{ $post['cost'] }}</span>
            @endif
        </div>
        
        <!-- 互动数据 -->
        <div class="flex justify-between text-xs text-gray-500">
            <div class="flex space-x-3">
                <span><i class="far fa-eye mr-1"></i>{{ $post['views'] }}</span>
                <span><i class="far fa-heart mr-1"></i>{{ $post['likes'] }}</span>
                <span><i class="far fa-comment mr-1"></i>{{ $post['comments'] }}</span>
            </div>
        </div>
    </div>
</div> 