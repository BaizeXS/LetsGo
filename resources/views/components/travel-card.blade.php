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
        <div class="flex items-center mb-3">
            <img src="{{ $post['user']['avatar'] }}" alt="{{ $post['user']['name'] }}" class="w-8 h-8 rounded-full mr-2">
            <span class="text-sm text-gray-700">{{ $post['user']['name'] }}</span>
        </div>
        
        <!-- Title -->
        <a href="{{ route('posts.show', $post['id']) }}" class="block">
            <h3 class="font-bold text-gray-900 mb-2 hover:text-red-500 transition">{{ $post['title'] }}</h3>
        </a>
        
        <!-- Tags -->
        <div class="flex items-center text-xs text-gray-500 mb-3">
            <span class="mr-3">{{ $post['duration'] }}</span>
            @if(isset($post['cost']))
            <span>{{ $post['cost'] }}</span>
            @endif
        </div>
        
        <!-- Interaction Data -->
        <div class="flex justify-between text-xs text-gray-500">
            <div class="flex space-x-3">
                <span><i class="far fa-eye mr-1"></i>{{ $post['views'] }}</span>
                <span><i class="far fa-heart mr-1"></i>{{ $post['likes'] }}</span>
                <span><i class="far fa-comment mr-1"></i>{{ $post['comments'] }}</span>
            </div>
            
            <!-- View Details Button -->
            <a href="{{ route('posts.show', $post['id']) }}" class="text-xs text-red-500 hover:text-red-600 transition">
                View Details <i class="fas fa-chevron-right ml-1 text-xs"></i>
            </a>
        </div>
    </div>
</div> 