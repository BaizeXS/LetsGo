@extends('layouts.app')

@section('content')
    <!-- 分类标签 -->
    <x-category-tabs :categories="$categories" :activeCategory="$activeCategory" />

    <!-- 卡片网格 -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-6" id="posts-container">
        @foreach($posts as $post)
            <div class="post-card">
                <x-travel-card :post="$post" />
            </div>
        @endforeach
    </div>
    
    <!-- 加载更多 -->
    <div class="flex justify-center mt-8">
        <button id="load-more" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-full">
            Load More
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
        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        
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
                        loadMoreBtn.textContent = 'Load More';
                    } else {
                        loadMoreBtn.textContent = 'All content loaded';
                        loadMoreBtn.disabled = true;
                    }
                });
        }, 1000);
    });
</script>
@endsection 