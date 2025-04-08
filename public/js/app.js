/**
 * LetsGO 旅游网站主JavaScript文件
 */

document.addEventListener('DOMContentLoaded', function() {
    // 初始化收藏按钮
    initFavoriteButtons();
    
    // 初始化滚动加载
    // initInfiniteScroll();
    
    // 初始化图片懒加载
    initLazyLoading();
});

/**
 * 初始化收藏按钮
 */
function initFavoriteButtons() {
    // 为所有收藏按钮添加动画效果
    document.querySelectorAll('.fa-heart').forEach(heart => {
        heart.addEventListener('click', function() {
            heart.classList.add('heart-animation');
            setTimeout(() => {
                heart.classList.remove('heart-animation');
            }, 600);
        });
    });
}

/**
 * 初始化无限滚动
 */
function initInfiniteScroll() {
    const postsContainer = document.getElementById('posts-container');
    let loading = false;
    let page = 1;
    
    window.addEventListener('scroll', function() {
        if (loading) return;
        
        // 检查是否已经滚动到底部
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500) {
            loading = true;
            page++;
            
            // 显示加载指示器
            const loadingIndicator = document.createElement('div');
            loadingIndicator.className = 'text-center py-4';
            loadingIndicator.innerHTML = '<div class="spinner mx-auto"></div>';
            postsContainer.appendChild(loadingIndicator);
            
            // 模拟请求数据
            setTimeout(() => {
                // 移除加载指示器
                postsContainer.removeChild(loadingIndicator);
                
                // 加载新的卡片内容
                // ...
                
                loading = false;
            }, 1500);
        }
    });
}

/**
 * 初始化图片懒加载
 */
function initLazyLoading() {
    // 检查是否支持IntersectionObserver
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const lazyImage = entry.target;
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImage.removeAttribute('data-src');
                    imageObserver.unobserve(lazyImage);
                }
            });
        });
        
        lazyImages.forEach(function(image) {
            imageObserver.observe(image);
        });
    } else {
        // 如果不支持，则使用简单的延迟加载方法
        // ...
    }
} 