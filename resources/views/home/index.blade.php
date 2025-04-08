@extends('layouts.app')

@section('content')
    <!-- 分类标签 -->
    <x-category-tabs :categories="$categories" :activeCategory="$activeCategory" />

    <!-- 卡片标签页 -->
    <div class="flex border-b border-gray-200 mb-6 overflow-x-auto">
        <button class="px-4 py-2 font-medium text-red-500 border-b-2 border-red-500">
            卡片1
        </button>
        <button class="px-4 py-2 font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700">
            卡片2
        </button>
        <button class="px-4 py-2 font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700">
            卡片3
        </button>
        <button class="px-4 py-2 font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700">
            卡片4
        </button>
        <button class="px-4 py-2 font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700">
            卡片5
        </button>
        <button class="px-4 py-2 font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700">
            更多...
        </button>
    </div>

    <!-- 卡片网格 -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="posts-container">
        @foreach($posts as $post)
            <div class="post-card">
                <x-travel-card :post="$post" />
            </div>
        @endforeach
    </div>
    
    <!-- 加载更多 -->
    <div class="flex justify-center mt-8">
        <button id="load-more" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-full">
            加载更多
        </button>
    </div>
@endsection

@section('scripts')
<script>
    // 切换收藏状态
    function toggleFavorite(el, postId) {
        const heartIcon = document.getElementById(`heart-${postId}`);
        
        if (heartIcon.classList.contains('far')) {
            heartIcon.classList.remove('far');
            heartIcon.classList.add('fas');
            // 这里可以添加AJAX请求到后端保存收藏
        } else {
            heartIcon.classList.remove('fas');
            heartIcon.classList.add('far');
            // 这里可以添加AJAX请求到后端移除收藏
        }
    }

    // 无限滚动加载
    let page = 2;
    const loadMoreBtn = document.getElementById('load-more');
    
    loadMoreBtn.addEventListener('click', function() {
        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 加载中...';
        
        // 模拟AJAX请求
        setTimeout(() => {
            // 在实际项目中，这里应该是从后端获取数据
            fetch(`/api/posts?page=${page}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        // 添加新的卡片到页面上
                        // ...
                        page++;
                        loadMoreBtn.textContent = '加载更多';
                    } else {
                        loadMoreBtn.textContent = '已加载全部内容';
                        loadMoreBtn.disabled = true;
                    }
                });
        }, 1000);
    });
</script>
@endsection 