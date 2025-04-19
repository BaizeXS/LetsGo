@extends('layouts.app')

@section('styles')
<style>
    /* Popup layer styles */
    .post-detail-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow-y: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .post-detail-content {
        width: 95%;
        max-width: 1200px;
        height: 90vh;
        background-color: white;
        border-radius: 10px;
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        grid-gap: 1rem;
        padding: 1rem;
    }
    
    /* Image carousel area */
    .image-slider {
        grid-column: 1;
        grid-row: 1;
        position: relative;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .slider-wrapper {
        display: flex;
        transition: transform 0.5s ease;
        height: 100%;
    }
    
    .slider-image {
        flex: 0 0 100%;
        object-fit: cover;
        width: 100%;
        height: 100%;
    }
    
    .slider-controls {
        position: absolute;
        bottom: 1rem;
        left: 0;
        right: 0;
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .slider-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.5);
        cursor: pointer;
    }
    
    .slider-dot.active {
        background-color: white;
    }
    
    .slider-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background-color: rgba(255, 255, 255, 0.5);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .slider-nav:hover {
        background-color: rgba(255, 255, 255, 0.8);
    }
    
    .slider-prev {
        left: 1rem;
    }
    
    .slider-next {
        right: 1rem;
    }
    
    /* Content area */
    .post-content {
        grid-column: 2;
        grid-row: 1;
        padding: 1rem;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }
    
    .post-title {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }
    
    .post-meta {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        color: #666;
    }
    
    .post-meta img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        margin-right: 0.5rem;
    }
    
    .post-info {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.875rem;
    }
    
    .post-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .post-tag {
        background-color: #f3f4f6;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
    }
    
    .post-description {
        flex-grow: 1;
        overflow-y: auto;
        line-height: 1.6;
    }
    
    /* Route map area */
    .route-map {
        grid-column: 1;
        grid-row: 2;
        border-radius: 8px;
        overflow: hidden;
        background-color: #f9fafb;
    }
    
    /* Comments area */
    .comments-section {
        grid-column: 2;
        grid-row: 2;
        padding: 1rem;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }
    
    .comments-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    
    .comments-list {
        overflow-y: auto;
        flex-grow: 1;
        max-height: calc(100% - 80px); /* Reduce space occupied by comment box */
        margin-bottom: 0.5rem;
    }
    
    .comment {
        padding: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
    }
    
    .comment-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 1rem;
    }
    
    .comment-content {
        flex-grow: 1;
    }
    
    .comment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .comment-text {
        margin-bottom: 0.5rem;
    }
    
    .comment-actions {
        display: flex;
        gap: 1rem;
        font-size: 0.75rem;
        color: #6b7280;
    }
    
    .comment-form {
        margin-top: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    @media (max-width: 768px) {
        .post-detail-content {
            grid-template-columns: 1fr;
            grid-template-rows: auto auto auto auto;
            height: auto;
            max-height: 90vh;
        }
        
        .image-slider {
            grid-column: 1;
            grid-row: 1;
            height: 40vh;
        }
        
        .post-content {
            grid-column: 1;
            grid-row: 2;
            max-height: 40vh;
        }
        
        .route-map {
            grid-column: 1;
            grid-row: 3;
            height: 30vh;
        }
        
        .comments-section {
            grid-column: 1;
            grid-row: 4;
            max-height: 40vh;
        }
    }
</style>

@include('partials.post-actions')
@include('partials.close-button')
@endsection

@section('content')
<!-- Pop-up detail page -->
<div class="post-detail-modal" id="post-detail-modal">
    <div class="post-detail-content">
        <!-- Close button -->
        <a href="{{ route('home') }}" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-white rounded-full shadow-md z-10 hover:bg-gray-100">
            <i class="fas fa-times"></i>
        </a>
        
        <!-- Image carousel area -->
        <div class="image-slider">
            <div class="slider-wrapper" id="slider-wrapper">
                @foreach($post['images'] as $image)
                    <img src="{{ $image }}" alt="{{ $post['title'] }}" class="slider-image">
                @endforeach
            </div>
            
            <div class="slider-controls">
                @foreach($post['images'] as $index => $image)
                    <div class="slider-dot {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}"></div>
                @endforeach
            </div>
            
            <div class="slider-nav slider-prev">
                <i class="fas fa-chevron-left"></i>
            </div>
            
            <div class="slider-nav slider-next">
                <i class="fas fa-chevron-right"></i>
            </div>
        </div>
        
        <!-- Content area -->
        <div class="post-content">
            <h1 class="post-title">{{ $post['title'] }}</h1>
            
            <div class="post-meta">
                <img src="{{ $post['user']['avatar'] }}" alt="{{ $post['user']['name'] }}">
                <div>
                    <div class="font-semibold">{{ $post['user']['name'] }}</div>
                    <div>{{ $post['created_at'] }}</div>
                </div>
            </div>
            
            <div class="post-info">
                <div><i class="fas fa-calendar"></i> {{ $post['duration'] }}</div>
                @if(isset($post['cost']))
                    <div><i class="fas fa-money-bill-wave"></i> {{ $post['cost'] }}</div>
                @endif
                <div><i class="fas fa-eye"></i> {{ $post['views'] }}</div>
            </div>
            
            @if(isset($post['tags']) && is_array($post['tags']))
                <div class="post-tags">
                    @foreach($post['tags'] as $tag)
                        <span class="post-tag">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
            
            <div class="post-description">
                {!! nl2br(e($post['content'])) !!}
            </div>
        </div>
        
        <!-- Route map area -->
        <div class="route-map" id="route-map">
            <div class="p-4 h-full flex flex-col justify-center items-center">
                <i class="fas fa-route text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-semibold mb-2">Travel Route</h3>
                <p class="text-gray-500 text-center">AI generated travel route map based on the itinerary.</p>
                <button id="generate-route" class="mt-4 bg-red-500 text-white px-4 py-2 rounded-full hover:bg-red-600 transition">
                    <i class="fas fa-magic mr-2"></i>Generate Route Map
                </button>
            </div>
        </div>
        
        <!-- Comments area -->
        <div class="comments-section">
            <div class="comments-header">
                <h2 class="text-lg font-semibold">Comments (<span id="comments-count">{{ count($post['comments']) }}</span>)</h2>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-heart"></i> <span id="likes-count">{{ $post['likes'] }}</span> Likes
                </div>
            </div>
            
            <div class="comments-list" id="comments-list">
                @foreach($post['comments'] as $comment)
                    <div class="comment" data-id="{{ $comment['id'] }}" data-user-id="{{ $comment['user']['id'] ?? 0 }}" data-likes="{{ $comment['likes'] ?? 0 }}" data-user-liked="{{ $comment['user_liked'] ?? false }}">
                        <img src="{{ $comment['user']['avatar'] }}" alt="{{ $comment['user']['name'] }}" class="comment-avatar">
                        
                        <div class="comment-content">
                            <div class="comment-header">
                                <div class="font-semibold">{{ $comment['user']['name'] }}</div>
                                <div class="text-sm text-gray-500">{{ $comment['created_at'] }}</div>
                            </div>
                            
                            <div class="comment-text">
                                {{ $comment['content'] }}
                            </div>
                            
                            <div class="comment-actions">
                                <button class="comment-like" data-id="{{ $comment['id'] }}">
                                    <i class="{{ isset($comment['user_liked']) && $comment['user_liked'] ? 'fas text-red-500' : 'far' }} fa-heart mr-1"></i> <span class="like-count">{{ $comment['likes'] ?? 0 }}</span> Like
                                </button>
                                <button class="comment-reply" data-id="{{ $comment['id'] }}">
                                    <i class="far fa-comment mr-1"></i> Reply
                                </button>
                                @if(auth()->check() && (auth()->id() == ($comment['user']['id'] ?? 0) || auth()->user()->isAdmin()))
                                <button class="comment-delete text-red-500" data-id="{{ $comment['id'] }}">
                                    <i class="far fa-trash-alt mr-1"></i> Delete
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @include('partials.comment-form')
        </div>
        
        <!-- Action buttons -->
        @include('partials.post-actions')
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image carousel functionality
        const sliderWrapper = document.getElementById('slider-wrapper');
        const sliderDots = document.querySelectorAll('.slider-dot');
        const prevBtn = document.querySelector('.slider-prev');
        const nextBtn = document.querySelector('.slider-next');
        let currentIndex = 0;
        const totalSlides = sliderDots.length;
        
        // Update carousel position
        function updateSlider() {
            sliderWrapper.style.transform = `translateX(-${currentIndex * 100}%)`;
            
            // Update indicators
            sliderDots.forEach((dot, index) => {
                if (index === currentIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }
        
        // Next image
        function nextSlide() {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateSlider();
        }
        
        // Previous image
        function prevSlide() {
            currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
            updateSlider();
        }
        
        // Click indicator to switch image
        sliderDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentIndex = index;
                updateSlider();
            });
        });
        
        // Click forward/back buttons
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        
        // Automatically switch images every 5 seconds
        setInterval(nextSlide, 5000);
        
        // Generate route map
        document.getElementById('generate-route').addEventListener('click', function() {
            const routeMapElement = document.getElementById('route-map');
            
            // Show loading
            routeMapElement.innerHTML = `
                <div class="p-4 h-full flex flex-col justify-center items-center">
                    <i class="fas fa-spinner fa-spin text-4xl text-yellow-500 mb-4"></i>
                    <p class="text-gray-500">Generating route map...</p>
                </div>
            `;
            
            // Call API to generate route map
            fetch('/api/generate-route-map', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    post_id: {{ $post['id'] }},
                    content: `{{ $post['content'] }}`
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    routeMapElement.innerHTML = `
                        <div class="h-full">
                            <img src="${data.map_url}" alt="Travel Route" class="w-full h-full object-contain">
                        </div>
                    `;
                } else {
                    routeMapElement.innerHTML = `
                        <div class="p-4 h-full flex flex-col justify-center items-center">
                            <i class="fas fa-exclamation-circle text-4xl text-red-500 mb-4"></i>
                            <p class="text-gray-500">Failed to generate route map</p>
                            <button id="retry-generate" class="mt-4 bg-red-500 text-white px-4 py-2 rounded-full hover:bg-red-600 transition">
                                Try Again
                            </button>
                        </div>
                    `;
                    
                    document.getElementById('retry-generate').addEventListener('click', function() {
                        document.getElementById('generate-route').click();
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                routeMapElement.innerHTML = `
                    <div class="p-4 h-full flex flex-col justify-center items-center">
                        <i class="fas fa-exclamation-circle text-4xl text-red-500 mb-4"></i>
                        <p class="text-gray-500">An error occurred. Please try again later.</p>
                        <button id="retry-generate" class="mt-4 bg-red-500 text-white px-4 py-2 rounded-full hover:bg-red-600 transition">
                            Try Again
                        </button>
                    </div>
                `;
                
                document.getElementById('retry-generate').addEventListener('click', function() {
                    document.getElementById('generate-route').click();
                });
            });
        });

        // Set like status
        const likeBtn = document.getElementById('like-btn');
        if (likeBtn && {{ isset($post['user_liked']) && $post['user_liked'] ? 'true' : 'false' }}) {
            likeBtn.classList.add('liked');
            likeBtn.querySelector('i').classList.remove('far');
            likeBtn.querySelector('i').classList.add('fas');
        }

        // Set favorite status
        const favoriteBtn = document.getElementById('favorite-btn');
        if (favoriteBtn && {{ isset($post['user_favorited']) && $post['user_favorited'] ? 'true' : 'false' }}) {
            favoriteBtn.classList.add('favorited');
            favoriteBtn.querySelector('i').classList.remove('far');
            favoriteBtn.querySelector('i').classList.add('fas');
        }
    });
</script>
@endsection