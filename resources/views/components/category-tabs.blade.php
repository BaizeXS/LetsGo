@props(['categories', 'activeCategory' => null])

<div class="categories-tabs-container overflow-x-auto py-2">
    <div class="flex space-x-4 min-w-max px-1">
        @foreach($categories as $category)
            <a 
                href="{{ route('home', ['category' => $category['slug']]) }}" 
                class="category-tab {{ $activeCategory == $category['slug'] ? 'text-red-500 border-red-500' : 'text-gray-600 border-transparent' }} 
                    px-3 py-2 text-sm font-medium border-b-2 whitespace-nowrap transition-colors"
            >
                {{ $category['name'] }}
            </a>
        @endforeach
    </div>
</div> 