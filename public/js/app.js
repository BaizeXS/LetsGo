/**
 * LetsGO Travel Website Main JavaScript File
 */

$(document).ready(function() {
    // Initialize favorite buttons
    initFavoriteButtons();
    
    // Initialize infinite scroll
    // initInfiniteScroll();
    
    // Initialize image lazy loading
    initLazyLoading();
});

/**
 * Initialize favorite buttons
 */
function initFavoriteButtons() {
    // Add animation effect to all favorite buttons
    $('.fa-heart').on('click', function() {
        $(this).addClass('heart-animation');
        setTimeout(() => {
            $(this).removeClass('heart-animation');
        }, 600);
    });
}

/**
 * Initialize infinite scroll
 */
function initInfiniteScroll() {
    const $postsContainer = $('#posts-container');
    let loading = false;
    let page = 1;
    
    $(window).on('scroll', function() {
        if (loading) return;
        
        // Check if scrolled to the bottom
        if ($(window).height() + $(window).scrollTop() >= $(document).height() - 500) {
            loading = true;
            page++;
            
            // Show loading indicator
            const $loadingIndicator = $('<div class="text-center py-4"><div class="spinner mx-auto"></div></div>');
            $postsContainer.append($loadingIndicator);
            
            // Simulate data request
            setTimeout(() => {
                // Remove loading indicator
                $loadingIndicator.remove();
                
                // Load new card content
                // ...
                
                loading = false;
            }, 1500);
        }
    });
}

/**
 * Initialize image lazy loading
 */
function initLazyLoading() {
    // Check if IntersectionObserver is supported
    if ('IntersectionObserver' in window) {
        const lazyImages = $('img[data-src]');
        
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const lazyImage = entry.target;
                    $(lazyImage).attr('src', $(lazyImage).data('src'));
                    $(lazyImage).removeAttr('data-src');
                    imageObserver.unobserve(lazyImage);
                }
            });
        });
        
        lazyImages.each(function() {
            imageObserver.observe(this);
        });
    } else {
        // If not supported, use simple delayed loading method
        // ...
    }
} 