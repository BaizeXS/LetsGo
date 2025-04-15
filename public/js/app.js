/**
 * LetsGO 旅游网站主JavaScript文件
 */

$(document).ready(function() {
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
    $('.fa-heart').on('click', function() {
        $(this).addClass('heart-animation');
        setTimeout(() => {
            $(this).removeClass('heart-animation');
        }, 600);
    });
}

/**
 * 初始化无限滚动
 */
function initInfiniteScroll() {
    const $postsContainer = $('#posts-container');
    let loading = false;
    let page = 1;
    
    $(window).on('scroll', function() {
        if (loading) return;
        
        // 检查是否已经滚动到底部
        if ($(window).height() + $(window).scrollTop() >= $(document).height() - 500) {
            loading = true;
            page++;
            
            // 显示加载指示器
            const $loadingIndicator = $('<div class="text-center py-4"><div class="spinner mx-auto"></div></div>');
            $postsContainer.append($loadingIndicator);
            
            // 模拟请求数据
            setTimeout(() => {
                // 移除加载指示器
                $loadingIndicator.remove();
                
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
        // 如果不支持，则使用简单的延迟加载方法
        // ...
    }
} 