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
        // Search box functionality
        const searchInput = document.querySelector('input[name="query"]');
        const searchResultsDropdown = document.getElementById('search-results-dropdown');
        const searchResultsList = document.getElementById('search-results-list');
        const searchLoading = document.getElementById('search-loading');
        const searchEmpty = document.getElementById('search-empty');
        const searchContainer = document.getElementById('search-container');
        
        // Position search results dropdown
        function positionDropdown() {
            // Get search box position information
            const rect = searchContainer.getBoundingClientRect();
            
            // Set dropdown position
            searchResultsDropdown.style.position = 'fixed';
            searchResultsDropdown.style.top = rect.bottom + 'px';
            searchResultsDropdown.style.left = rect.left + 'px';
            searchResultsDropdown.style.width = rect.width + 'px';
        }
        
        // Reposition dropdown when window is resized
        window.addEventListener('resize', function() {
            if (!searchResultsDropdown.classList.contains('hidden')) {
                positionDropdown();
            }
        });
        
        let searchTimeout;

        // Listen for search input
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            // Clear previous timer
            clearTimeout(searchTimeout);
            
            if (query.length > 1) {
                // Set timer to prevent frequent requests
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            } else {
                hideSearchResults();
            }
        });

        // Prevent search form submission, show real-time search results instead
        document.getElementById('search-form').addEventListener('submit', function(e) {
            const query = searchInput.value.trim();
            if (query.length <= 1) {
                e.preventDefault();
            }
        });

        // Hide dropdown when clicking elsewhere on the document
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResultsDropdown.contains(e.target)) {
                hideSearchResults();
            }
        });

        // Perform search
        function performSearch(query) {
            // Show loading state
            searchResultsDropdown.classList.remove('hidden');
            positionDropdown();
            searchLoading.classList.remove('hidden');
            searchEmpty.classList.add('hidden');
            searchResultsList.innerHTML = '';
            
            // Hide view all link
            const viewAllSection = document.getElementById('search-view-all');
            viewAllSection.classList.add('hidden');
            
            // Send AJAX request
            fetch(`/api/search?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchLoading.classList.add('hidden');
                    
                    if (data.posts.length === 0) {
                        searchEmpty.classList.remove('hidden');
                    } else {
                        // Render search results
                        data.posts.forEach(post => {
                            const resultItem = document.createElement('div');
                            resultItem.className = 'py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors';
                            
                            // Highlight matching text
                            let highlightedTitle = post.title;
                            if (data.query) {
                                // Create regex to replace query with highlighted version
                                const regex = new RegExp(`(${escapeRegExp(data.query)})`, 'gi');
                                highlightedTitle = post.title.replace(regex, '<span class="bg-yellow-200">$1</span>');
                            }
                            
                            // Highlight query words in excerpt
                            let highlightedExcerpt = post.excerpt || '';
                            if (data.query && highlightedExcerpt) {
                                const regex = new RegExp(`(${escapeRegExp(data.query)})`, 'gi');
                                highlightedExcerpt = highlightedExcerpt.replace(regex, '<span class="bg-yellow-200">$1</span>');
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
                            searchResultsList.appendChild(resultItem);
                        });
                        
                        // Show "View all" link
                        if (data.posts.length >= 5) {
                            viewAllSection.classList.remove('hidden');
                            const viewAllLink = document.getElementById('search-view-all-link');
                            viewAllLink.href = `/search?query=${encodeURIComponent(query)}`;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching search results:', error);
                    searchLoading.classList.add('hidden');
                    searchEmpty.classList.remove('hidden');
                    searchEmpty.textContent = 'Error loading search results';
                });
        }

        // Helper function: Escape special characters in regex
        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        // Hide search results
        function hideSearchResults() {
            searchResultsDropdown.classList.add('hidden');
            searchResultsList.innerHTML = '';
        }

        // Generic favorite button event handler
        function handleFavoriteClick(button) {
            const postId = button.getAttribute('data-post-id');
            const heartIcon = document.getElementById(`heart-${postId}`);
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Disable button to prevent repeated clicks
            button.disabled = true;
            
            // Show status before sending request
            showNotification('Processing...', 'info');
            
            // Use simplified URL request method
            fetch(`/posts/${postId}/favorite`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                // First check response status
                const contentType = response.headers.get('content-type');
                console.log('Response headers:', [...response.headers.entries()]);
                console.log('Response status:', response.status);
                console.log('Content type:', contentType);
                
                if (response.status === 401) {
                    // User not logged in, redirect to login page
                    window.location.href = '/login';
                    throw new Error('Please login to favorite posts');
                }
                
                if (!response.ok) {
                    throw new Error(`Server responded with ${response.status}: ${response.statusText}`);
                }
                
                try {
                    return response.json();
                } catch (err) {
                    console.error('JSON parsing error:', err);
                    return { success: false, message: 'Invalid response from server' };
                }
            })
            .then(data => {
                if (data && data.success) {
                    // Update heart icon
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
                } else {
                    // Handle unsuccessful response
                    showNotification(data?.message || 'Operation failed', 'error');
                }
            })
            .catch(error => {
                // Handle error
                console.error('Error:', error);
                showNotification(error.message || 'An error occurred', 'error');
            })
            .finally(() => {
                // Re-enable button
                button.disabled = false;
            });
        }

        // Attach event listeners to all favorite buttons
        function attachFavoriteEvents() {
            const favoriteButtons = document.querySelectorAll('.favorite-btn:not([data-event-attached])');
            
            favoriteButtons.forEach(button => {
                button.setAttribute('data-event-attached', 'true');
                
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent event bubbling
                    handleFavoriteClick(this);
                });
            });
        }

        // Initialize favorite buttons on the page
        attachFavoriteEvents();
        
        // Notification helper function
        function showNotification(message, type = 'success') {
            // Remove all existing notifications
            const existingNotifications = document.querySelectorAll('.notification-message');
            existingNotifications.forEach(notification => {
                notification.remove();
            });
            
            const notification = document.createElement('div');
            notification.className = `notification-message fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white shadow-lg transition-opacity duration-500 z-50`;
            
            // Set background color based on type
            switch (type) {
                case 'success':
                    notification.classList.add('bg-green-500');
                    break;
                case 'error':
                    notification.classList.add('bg-red-500');
                    break;
                case 'info':
                    notification.classList.add('bg-blue-500');
                    break;
                default:
                    notification.classList.add('bg-gray-500');
            }
            
            notification.innerHTML = message;
            document.body.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }, 3000);
        }
        
        // Load more global function
        window.attachFavoriteEvents = attachFavoriteEvents;
        window.showNotification = showNotification;
    });

    // Attach favorite events to new favorite buttons (for dynamic content loading)
    function attachFavoriteEvents() {
        const favoriteButtons = document.querySelectorAll('.favorite-btn:not([data-event-attached])');
        
        favoriteButtons.forEach(button => {
            button.setAttribute('data-event-attached', 'true');
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Prevent event bubbling
                
                const postId = this.getAttribute('data-post-id');
                const heartIcon = document.getElementById(`heart-${postId}`);
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Disable button to prevent repeated clicks
                this.disabled = true;
                
                // Show status before sending request
                if (typeof window.showNotification === 'function') {
                    window.showNotification('Processing...', 'info');
                }
                
                // Send AJAX request to toggle favorite
                fetch(`/posts/${postId}/favorite`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    // First check response status
                    const contentType = response.headers.get('content-type');
                    console.log('Response headers:', [...response.headers.entries()]);
                    console.log('Response status:', response.status);
                    console.log('Content type:', contentType);
                    
                    if (response.status === 401) {
                        // User not logged in, redirect to login page
                        window.location.href = '/login';
                        throw new Error('Please login to favorite posts');
                    }
                    
                    if (!response.ok) {
                        throw new Error(`Server responded with ${response.status}: ${response.statusText}`);
                    }
                    
                    try {
                        return response.json();
                    } catch (err) {
                        console.error('JSON parsing error:', err);
                        return { success: false, message: 'Invalid response from server' };
                    }
                })
                .then(data => {
                    if (data && data.success) {
                        // Update heart icon
                        if (data.isFavorite) {
                            heartIcon.classList.remove('far');
                            heartIcon.classList.add('fas');
                            this.setAttribute('data-is-favorite', 'true');
                            
                            // Show success notification
                            if (typeof window.showNotification === 'function') {
                                window.showNotification('Post added to favorites', 'success');
                            }
                        } else {
                            heartIcon.classList.remove('fas');
                            heartIcon.classList.add('far');
                            this.setAttribute('data-is-favorite', 'false');
                            
                            // Show success notification
                            if (typeof window.showNotification === 'function') {
                                window.showNotification('Post removed from favorites', 'success');
                            }
                        }
                    } else {
                        // Handle unsuccessful response
                        if (typeof window.showNotification === 'function') {
                            window.showNotification(data?.message || 'Operation failed', 'error');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof window.showNotification === 'function') {
                        window.showNotification(error.message || 'An error occurred', 'error');
                    }
                })
                .finally(() => {
                    // Re-enable button
                    this.disabled = false;
                });
            });
        });
    }
    
    // Helper function: Notification display (global available)
    function showNotification(message, type = 'success') {
        // If internal function exists, use internal function
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
            return;
        }
        
        // Remove all existing notifications
        const existingNotifications = document.querySelectorAll('.notification-message');
        existingNotifications.forEach(notification => {
            notification.remove();
        });
        
        const notification = document.createElement('div');
        notification.className = `notification-message fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white shadow-lg transition-opacity duration-500 z-50`;
        
        // Set background color based on type
        switch (type) {
            case 'success':
                notification.classList.add('bg-green-500');
                break;
            case 'error':
                notification.classList.add('bg-red-500');
                break;
            case 'info':
                notification.classList.add('bg-blue-500');
                break;
            default:
                notification.classList.add('bg-gray-500');
        }
        
        notification.innerHTML = message;
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 500);
        }, 3000);
    }
    
    // Infinite scroll load
    let page = 2;
    const loadMoreBtn = document.getElementById('load-more');
    
    loadMoreBtn.addEventListener('click', function() {
        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        
        // Send AJAX request to get more posts
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
                    const postsContainer = document.getElementById('posts-container');
                    
                    data.posts.forEach(post => {
                        const postCard = document.createElement('div');
                        postCard.className = 'post-card';
                        
                        // Note: Here we use a simplified version of the card template, adjust based on actual project
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
                    
                    // Attach favorite button event listeners
                    if (typeof window.attachFavoriteEvents === 'function') {
                        window.attachFavoriteEvents();
                    } else {
                        // Backup plan
                        attachFavoriteEvents();
                    }
                } else {
                    loadMoreBtn.textContent = 'All content loaded';
                    loadMoreBtn.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                loadMoreBtn.textContent = 'Error loading more content';
                showNotification('Failed to load more content: ' + error.message, 'error');
            });
    });
</script>
@endsection 