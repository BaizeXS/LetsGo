@extends('layouts.app')

@section('content')
    <!-- 搜索结果下拉框 -->
    <div id="search-results-dropdown" class="hidden w-full max-w-md absolute z-50 bg-white rounded-xl shadow-lg max-h-96 overflow-y-auto">
        <div class="p-4">
            <div id="search-loading" class="hidden flex justify-center py-2">
                <i class="fas fa-spinner fa-spin text-gray-400"></i>
            </div>
            <div id="search-empty" class="hidden py-3 text-center text-gray-500">
                No results found
            </div>
            <div id="search-results-list">
                <!-- 搜索结果将动态填充在这里 -->
            </div>
        </div>
    </div>

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
        // 搜索框功能
        const searchInput = document.querySelector('input[name="query"]');
        const searchResultsDropdown = document.getElementById('search-results-dropdown');
        const searchResultsList = document.getElementById('search-results-list');
        const searchLoading = document.getElementById('search-loading');
        const searchEmpty = document.getElementById('search-empty');
        const searchContainer = document.getElementById('search-container');
        
        // 定位搜索结果下拉框
        function positionDropdown() {
            // 获取搜索框的位置信息
            const rect = searchContainer.getBoundingClientRect();
            
            // 设置下拉框的位置
            searchResultsDropdown.style.position = 'fixed';
            searchResultsDropdown.style.top = rect.bottom + 'px';
            searchResultsDropdown.style.left = rect.left + 'px';
            searchResultsDropdown.style.width = rect.width + 'px';
        }
        
        // 窗口调整大小时重新定位下拉框
        window.addEventListener('resize', function() {
            if (!searchResultsDropdown.classList.contains('hidden')) {
                positionDropdown();
            }
        });
        
        let searchTimeout;

        // 监听搜索框输入
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            // 清除之前的定时器
            clearTimeout(searchTimeout);
            
            if (query.length > 1) {
                // 设置定时器，防止频繁请求
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            } else {
                hideSearchResults();
            }
        });

        // 阻止搜索表单提交，改为显示实时搜索结果
        document.getElementById('search-form').addEventListener('submit', function(e) {
            const query = searchInput.value.trim();
            if (query.length <= 1) {
                e.preventDefault();
            }
        });

        // 点击文档其他地方隐藏下拉框
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResultsDropdown.contains(e.target)) {
                hideSearchResults();
            }
        });

        // 执行搜索
        function performSearch(query) {
            // 显示加载状态
            searchResultsDropdown.classList.remove('hidden');
            positionDropdown();
            searchLoading.classList.remove('hidden');
            searchEmpty.classList.add('hidden');
            searchResultsList.innerHTML = '';
            
            // 发送AJAX请求
            fetch(`/api/search?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchLoading.classList.add('hidden');
                    
                    if (data.posts.length === 0) {
                        searchEmpty.classList.remove('hidden');
                    } else {
                        // 渲染搜索结果
                        data.posts.forEach(post => {
                            const resultItem = document.createElement('div');
                            resultItem.className = 'py-2 border-b border-gray-100 last:border-0';
                            resultItem.innerHTML = `
                                <a href="/posts/${post.id}" class="flex items-center hover:bg-gray-50 p-2 rounded">
                                    <div class="w-16 h-16 bg-gray-200 rounded overflow-hidden mr-3">
                                        <img src="${post.cover_image}" alt="${post.title}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900">${post.title}</h3>
                                        <p class="text-sm text-gray-500">${post.destination || '未知目的地'}</p>
                                    </div>
                                </a>
                            `;
                            searchResultsList.appendChild(resultItem);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching search results:', error);
                    searchLoading.classList.add('hidden');
                    searchEmpty.classList.remove('hidden');
                    searchEmpty.textContent = 'Error loading search results';
                });
        }

        // 隐藏搜索结果
        function hideSearchResults() {
            searchResultsDropdown.classList.add('hidden');
            searchResultsList.innerHTML = '';
        }

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
        
        // 发送AJAX请求获取更多帖子
        fetch(`/api/posts?page=${page}`)
            .then(response => response.json())
            .then(data => {
                if (data.posts && data.posts.length > 0) {
                    // 将新帖子添加到容器中
                    const postsContainer = document.getElementById('posts-container');
                    
                    data.posts.forEach(post => {
                        const postCard = document.createElement('div');
                        postCard.className = 'post-card';
                        
                        // 注意：这里使用简化版的卡片模板，根据实际项目进行调整
                        postCard.innerHTML = `
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                                <a href="/posts/${post.id}">
                                    <div class="relative pb-[66%]">
                                        <img src="${post.cover_image}" alt="${post.title}" class="absolute inset-0 w-full h-full object-cover">
                                    </div>
                                </a>
                                <div class="p-3">
                                    <a href="/posts/${post.id}" class="block">
                                        <h3 class="text-sm font-medium text-gray-900 truncate">${post.title}</h3>
                                    </a>
                                    <div class="flex justify-between items-center mt-2">
                                        <div class="flex space-x-1 text-xs text-gray-500">
                                            <span><i class="far fa-eye"></i> ${post.views}</span>
                                            <span><i class="far fa-heart"></i> ${post.likes}</span>
                                        </div>
                                        <button class="favorite-btn text-xs text-gray-400" data-post-id="${post.id}" data-is-favorite="${post.is_favorite ? 'true' : 'false'}">
                                            <i id="heart-${post.id}" class="${post.is_favorite ? 'fas' : 'far'} fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        postsContainer.appendChild(postCard);
                    });
                    
                    page++;
                    loadMoreBtn.textContent = 'Load More';
                    
                    // 添加收藏按钮事件监听
                    attachFavoriteEvents();
                } else {
                    loadMoreBtn.textContent = 'All content loaded';
                    loadMoreBtn.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                loadMoreBtn.textContent = 'Error loading more content';
            });
    });
    
    // 为新添加的收藏按钮绑定事件
    function attachFavoriteEvents() {
        const favoriteButtons = document.querySelectorAll('.favorite-btn:not([data-event-attached])');
        
        favoriteButtons.forEach(button => {
            button.setAttribute('data-event-attached', 'true');
            
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
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the heart icon
                        if (data.isFavorite) {
                            heartIcon.classList.remove('far');
                            heartIcon.classList.add('fas');
                            button.setAttribute('data-is-favorite', 'true');
                        } else {
                            heartIcon.classList.remove('fas');
                            heartIcon.classList.add('far');
                            button.setAttribute('data-is-favorite', 'false');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    }
</script>
@endsection 