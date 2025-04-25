@props(['post'])

<div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
    <!-- Cover Image -->
    <div class="relative">
        <a href="{{ route('posts.show', $post['id']) }}" class="block">
            <img src="{{ $post['cover_image'] }}" alt="{{ $post['title'] }}" class="w-full h-48 object-cover">
        </a>
        
        <!-- Favorite Button -->
        <button 
            class="favorite-btn absolute top-3 right-3 bg-white bg-opacity-70 hover:bg-opacity-100 rounded-full p-2 transition-all duration-200"
            data-post-id="{{ $post['id'] }}"
            data-is-favorite="{{ isset($post['is_favorite']) && $post['is_favorite'] ? 'true' : 'false' }}"
        >
            <i class="{{ isset($post['is_favorite']) && $post['is_favorite'] ? 'fas' : 'far' }} fa-heart text-red-500" id="heart-{{ $post['id'] }}"></i>
        </button>
    </div>
    
    <!-- Card Content -->
    <div class="p-4">
        <!-- User Info -->
        <div class="flex items-center mb-2">
            <img src="{{ $post['user']['avatar'] }}" alt="{{ $post['user']['name'] }}" class="w-7 h-7 rounded-full mr-2">
            <span class="text-xs text-gray-600 truncate">{{ $post['user']['name'] }}</span>
        </div>
        
        <!-- Title -->
        <a href="{{ route('posts.show', $post['id']) }}" class="block">
            <h3 class="font-bold text-gray-900 mb-2 hover:text-red-500 transition text-sm line-clamp-2">{{ $post['title'] }}</h3>
        </a>
        
        <!-- Tags -->
        <div class="flex items-center text-xs text-gray-500 mb-3">
            <span class="px-2 py-1 bg-gray-100 rounded-full mr-2">{{ $post['duration'] }}</span>
            @if(isset($post['cost']))
            <span class="px-2 py-1 bg-gray-100 rounded-full">{{ $post['cost'] }}</span>
            @endif
        </div>
        
        <!-- Divider -->
        <div class="border-t border-gray-100 mb-2"></div>
        
        <!-- Interaction Data -->
        <div class="flex justify-between items-center text-xs text-gray-500">
            <div class="flex space-x-3">
                <span class="flex items-center"><i class="far fa-eye mr-1"></i>{{ $post['views'] }}</span>
                <span class="flex items-center"><i class="far fa-heart mr-1"></i>{{ $post['likes'] }}</span>
                <span class="flex items-center"><i class="far fa-comment mr-1"></i>{{ $post['comments'] }}</span>
            </div>
            
            <!-- Icon Only Link -->
            <a href="{{ route('posts.show', $post['id']) }}" class="text-red-500 hover:text-red-600 transition hover:bg-gray-100 p-1 rounded-full">
                <i class="fas fa-chevron-right text-xs"></i>
            </a>
        </div>
    </div>
</div> 