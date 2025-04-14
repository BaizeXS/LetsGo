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
    document.addEventListener('DOMContentLoaded', function() {
        // Handle favorite button clicks
        const favoriteButtons = document.querySelectorAll('.favorite-btn');
        
        favoriteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const postId = this.getAttribute('data-post-id');
                const heartIcon = document.getElementById(`heart-${postId}`);
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // 创建FormData对象来发送数据
                const formData = new FormData();
                formData.append('_token', token);
                
                // Send AJAX request to toggle favorite
                fetch(`/posts/${postId}/favorite`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                    },
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        if (response.status === 401) {
                            // User not logged in, redirect to login
                            window.location.href = '/login';
                            throw new Error('Please login to favorite posts');
                        }
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update the heart icon
                        if (data.isFavorite) {
                            heartIcon.classList.remove('far');
                            heartIcon.classList.add('fas');
                            button.setAttribute('data-is-favorite', 'true');
                            
                            // Show success notification
                            showNotification('Post added to favorites', 'success');
                        } else {
                            heartIcon.classList.remove('fas');
                            heartIcon.classList.add('far');
                            button.setAttribute('data-is-favorite', 'false');
                            
                            // Show success notification
                            showNotification('Post removed from favorites', 'success');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification(error.message, 'error');
                });
            });
        });
        
        // Notification helper function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} shadow-lg transition-opacity duration-500 z-50`;
            notification.innerHTML = message;
            document.body.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 500);
            }, 3000);
        }
    });

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