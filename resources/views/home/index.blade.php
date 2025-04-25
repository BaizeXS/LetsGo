@extends('layouts.app')

@section('content')
    <!-- Search results dropdown -->
    <div id="search-results-dropdown" class="hidden w-full max-w-md absolute z-50 bg-white rounded-xl shadow-lg max-h-96 overflow-y-auto border border-gray-200">
        <div class="p-2">
            <div id="search-loading" class="hidden flex justify-center py-4">
                <i class="fas fa-spinner fa-spin text-gray-400"></i>
            </div>
            <div id="search-empty" class="hidden py-5 text-center text-gray-500">
                <i class="fas fa-search mb-2 text-gray-300 text-lg"></i>
                <p>No results found for your search</p>
                <p class="text-xs mt-1 text-gray-400">Try different keywords or browse categories</p>
            </div>
            <div id="search-results-list">
                <!-- Search results will be dynamically filled here -->
            </div>
            <div id="search-view-all" class="hidden py-3 text-center border-t border-gray-100">
                <a href="#" id="search-view-all-link" class="text-sm text-red-500 hover:text-red-600 transition flex items-center justify-center">
                    <span>View all results</span>
                    <i class="fas fa-chevron-right text-xs ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Category tabs -->
    <x-category-tabs :categories="$categories" :activeCategory="$activeCategory" />

    <!-- Card grid -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-6" id="posts-container">
        @foreach($posts as $post)
            <div class="post-card">
                <x-travel-card :post="$post" />
            </div>
        @endforeach
    </div>
    
    <!-- Load more -->
    <div class="flex justify-center mt-8">
        <button id="load-more" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-full">
            Load More
        </button>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // DOM elements
        const elements = {
            searchInput: document.querySelector('input[name="query"]'),
            searchResultsDropdown: document.getElementById('search-results-dropdown'),
            searchResultsList: document.getElementById('search-results-list'),
            searchLoading: document.getElementById('search-loading'),
            searchEmpty: document.getElementById('search-empty'),
            searchContainer: document.getElementById('search-container'),
            searchForm: document.getElementById('search-form'),
            viewAllSection: document.getElementById('search-view-all'),
            viewAllLink: document.getElementById('search-view-all-link'),
            loadMoreBtn: document.getElementById('load-more'),
            postsContainer: document.getElementById('posts-container')
        };

        // Initialize variables
        let searchTimeout;
        let page = 2;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // SEARCH FUNCTIONALITY
        
        // Position search results dropdown
        function positionDropdown() {
            const rect = elements.searchContainer.getBoundingClientRect();
            elements.searchResultsDropdown.style.position = 'fixed';
            elements.searchResultsDropdown.style.top = rect.bottom + 'px';
            elements.searchResultsDropdown.style.left = rect.left + 'px';
            elements.searchResultsDropdown.style.width = rect.width + 'px';
        }
        
        // Reposition dropdown when window is resized
        window.addEventListener('resize', function() {
            if (!elements.searchResultsDropdown.classList.contains('hidden')) {
                positionDropdown();
            }
        });
        
        // Listen for search input
        elements.searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(searchTimeout);
            
            if (query.length > 1) {
                searchTimeout = setTimeout(() => performSearch(query), 300);
            } else {
                hideSearchResults();
            }
        });

        // Prevent search form submission for short queries
        elements.searchForm.addEventListener('submit', function(e) {
            const query = elements.searchInput.value.trim();
            if (query.length <= 1) {
                e.preventDefault();
            }
        });

        // Hide dropdown when clicking elsewhere
        document.addEventListener('click', function(e) {
            if (!elements.searchInput.contains(e.target) && !elements.searchResultsDropdown.contains(e.target)) {
                hideSearchResults();
            }
        });

        // Perform search
        function performSearch(query) {
            // Show loading state
            elements.searchResultsDropdown.classList.remove('hidden');
            positionDropdown();
            elements.searchLoading.classList.remove('hidden');
            elements.searchEmpty.classList.add('hidden');
            elements.searchResultsList.innerHTML = '';
            elements.viewAllSection.classList.add('hidden');
            
            // Send AJAX request
            fetch(`/api/search?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    elements.searchLoading.classList.add('hidden');
                    
                    if (data.posts.length === 0) {
                        elements.searchEmpty.classList.remove('hidden');
                        return;
                    }
                    
                    // Render search results
                    data.posts.forEach(post => {
                        const resultItem = document.createElement('div');
                        resultItem.className = 'py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors';
                        
                        // Highlight matching text
                        let highlightedTitle = post.title;
                        let highlightedExcerpt = post.excerpt || '';
                        
                        if (data.query) {
                            const regex = new RegExp(`(${escapeRegExp(data.query)})`, 'gi');
                            highlightedTitle = post.title.replace(regex, '<span class="bg-yellow-200">$1</span>');
                            if (highlightedExcerpt) {
                                highlightedExcerpt = highlightedExcerpt.replace(regex, '<span class="bg-yellow-200">$1</span>');
                            }
                        }
                        
                        resultItem.innerHTML = `
                            <a href="/posts/${post.id}" class="flex group">
                                <div class="w-20 h-20 bg-gray-200 rounded overflow-hidden mr-3 flex-shrink-0">
                                    <img src="${post.cover_image}" alt="${post.title}" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <h3 class="font-medium text-gray-900 text-sm group-hover:text-red-500 transition line-clamp-1">${highlightedTitle}</h3>
                                    <p class="text-xs text-gray-500 mb-1">${post.destination || 'Unknown destination'} · ${post.user.name}</p>
                                    <p class="text-xs text-gray-600 line-clamp-2">${highlightedExcerpt}</p>
                                </div>
                            </a>
                        `;
                        elements.searchResultsList.appendChild(resultItem);
                    });
                    
                    // Show "View all" link
                    if (data.posts.length >= 5) {
                        elements.viewAllSection.classList.remove('hidden');
                        elements.viewAllLink.href = `/search?query=${encodeURIComponent(query)}`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching search results:', error);
                    elements.searchLoading.classList.add('hidden');
                    elements.searchEmpty.classList.remove('hidden');
                    elements.searchEmpty.textContent = 'Error loading search results';
                });
        }

        // Hide search results
        function hideSearchResults() {
            elements.searchResultsDropdown.classList.add('hidden');
            elements.searchResultsList.innerHTML = '';
        }

        // FAVORITE FUNCTIONALITY
        
        // Handle favorite button click
        function handleFavoriteClick(button) {
            const postId = button.getAttribute('data-post-id');
            const heartIcon = document.getElementById(`heart-${postId}`);
            
            // Disable button to prevent repeated clicks
            button.disabled = true;
            showNotification('Processing...', 'info');
            
            // Send request to toggle favorite
            fetch(`/posts/${postId}/favorite`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (response.status === 401) {
                    window.location.href = '/login';
                    throw new Error('Please login to favorite posts');
                }
                
                if (!response.ok) {
                    throw new Error(`Server responded with ${response.status}: ${response.statusText}`);
                }
                
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    // Update heart icon
                    if (data.isFavorite) {
                        heartIcon.classList.remove('far');
                        heartIcon.classList.add('fas');
                        button.setAttribute('data-is-favorite', 'true');
                        showNotification('Post added to favorites', 'success');
                    } else {
                        heartIcon.classList.remove('fas');
                        heartIcon.classList.add('far');
                        button.setAttribute('data-is-favorite', 'false');
                        showNotification('Post removed from favorites', 'success');
                    }
                } else {
                    showNotification(data?.message || 'Operation failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(error.message || 'An error occurred', 'error');
            })
            .finally(() => {
                button.disabled = false;
            });
        }

        // Attach event listeners to favorite buttons
        function attachFavoriteEvents() {
            const favoriteButtons = document.querySelectorAll('.favorite-btn:not([data-event-attached])');
            
            favoriteButtons.forEach(button => {
                button.setAttribute('data-event-attached', 'true');
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    handleFavoriteClick(this);
                });
            });
        }

        // Initialize favorite buttons
        attachFavoriteEvents();
        
        // NOTIFICATION FUNCTIONALITY
        
        // Display notification message
        function showNotification(message, type = 'success') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification-message');
            existingNotifications.forEach(notification => notification.remove());
            
            // Create new notification
            const notification = document.createElement('div');
            notification.className = `notification-message fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white shadow-lg transition-opacity duration-500 z-50`;
            
            // Set background color based on type
            const bgClasses = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500',
                default: 'bg-gray-500'
            };
            
            notification.classList.add(bgClasses[type] || bgClasses.default);
            notification.innerHTML = message;
            document.body.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }
        
        // LOAD MORE FUNCTIONALITY
        
        // Load more posts
        elements.loadMoreBtn.addEventListener('click', function() {
            elements.loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            
            fetch(`/api/posts?page=${page}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.posts && data.posts.length > 0) {
                        // Add new posts to container
                        data.posts.forEach(post => {
                            const postCard = document.createElement('div');
                            postCard.className = 'post-card';
                            
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
                            
                            elements.postsContainer.appendChild(postCard);
                        });
                        
                        page++;
                        elements.loadMoreBtn.textContent = 'Load More';
                        attachFavoriteEvents();
                    } else {
                        elements.loadMoreBtn.textContent = 'All content loaded';
                        elements.loadMoreBtn.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    elements.loadMoreBtn.textContent = 'Error loading more content';
                    showNotification('Failed to load more content: ' + error.message, 'error');
                });
        });
        
        // HELPER FUNCTIONS
        
        // Escape special characters in regex
        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }
        
        // Expose functions globally
        window.showNotification = showNotification;
        window.attachFavoriteEvents = attachFavoriteEvents;
    });
</script>
@endsection 