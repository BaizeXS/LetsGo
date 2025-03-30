/**
 * Let's Go 智能旅游综合服务平台 - 主JavaScript文件
 */

// 等待文档加载完成
document.addEventListener('DOMContentLoaded', function() {
    // 初始化所有组件
    initNavbar();
    initHeroSlider();
    initDestinationSlider();
    initScrollAnimations();
    initRatingStars();
    initMobileMenu();
});

/**
 * 导航栏初始化
 */
function initNavbar() {
    // 监听滚动事件，使导航栏在滚动时添加阴影
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            if (window.scrollY > 10) {
                navbar.classList.add('shadow-md');
            } else {
                navbar.classList.remove('shadow-md');
            }
        }
    });

    // 导航菜单激活状态处理
    const currentLocation = window.location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav a');
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentLocation) {
            link.classList.add('text-primary');
            link.classList.add('font-bold');
        }
    });
}

/**
 * Hero区域轮播初始化
 */
function initHeroSlider() {
    const heroElement = document.querySelector('.hero');
    if (!heroElement) return;

    // 轮播图片数组 (实际项目中可能会从CMS或API获取)
    const backgroundImages = [
        'images/hero-bg-1.jpg',
        'images/hero-bg-2.jpg',
        'images/hero-bg-3.jpg'
    ];

    let currentImageIndex = 0;

    // 更换背景图片的函数
    function changeBackgroundImage() {
        // 淡出效果
        heroElement.style.opacity = 0;
        
        setTimeout(() => {
            // 更换图片
            heroElement.style.backgroundImage = `url(${backgroundImages[currentImageIndex]})`;
            
            // 淡入效果
            heroElement.style.opacity = 1;
            
            // 更新索引
            currentImageIndex = (currentImageIndex + 1) % backgroundImages.length;
        }, 500);
    }

    // 设置初始背景图
    if (backgroundImages.length > 0) {
        heroElement.style.backgroundImage = `url(${backgroundImages[0]})`;
        heroElement.style.transition = 'opacity 0.5s ease-in-out';
    }

    // 每7秒更换一次背景图
    if (backgroundImages.length > 1) {
        setInterval(changeBackgroundImage, 7000);
    }
}

/**
 * 推荐目的地滑动效果初始化
 */
function initDestinationSlider() {
    const slider = document.querySelector('.destination-slider');
    if (!slider) return;

    const sliderContainer = slider.querySelector('.destination-slider-container');
    const prevButton = slider.querySelector('.slider-prev');
    const nextButton = slider.querySelector('.slider-next');
    
    if (!sliderContainer || !prevButton || !nextButton) return;

    // 滑动到下一张的函数
    function slideNext() {
        sliderContainer.scrollBy({
            left: sliderContainer.clientWidth,
            behavior: 'smooth'
        });
    }

    // 滑动到上一张的函数
    function slidePrev() {
        sliderContainer.scrollBy({
            left: -sliderContainer.clientWidth,
            behavior: 'smooth'
        });
    }

    // 绑定按钮事件
    nextButton.addEventListener('click', slideNext);
    prevButton.addEventListener('click', slidePrev);
}

/**
 * 滚动动画初始化
 */
function initScrollAnimations() {
    // 获取所有需要添加动画的元素
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    // 如果没有动画元素，则直接返回
    if (animatedElements.length === 0) return;

    // 检查元素是否在可视区域内的函数
    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.bottom >= 0
        );
    }

    // 处理滚动事件，判断哪些元素应该显示动画
    function handleScroll() {
        animatedElements.forEach(element => {
            if (isElementInViewport(element) && !element.classList.contains('animated')) {
                element.classList.add('animated');
                
                // 根据动画类型添加不同的动画类
                if (element.classList.contains('fade-in')) {
                    element.classList.add('animate-fadeIn');
                } else if (element.classList.contains('slide-up')) {
                    element.classList.add('animate-slideUp');
                }
            }
        });
    }

    // 初始检查，页面加载时就显示在视口内的元素动画
    handleScroll();

    // 监听滚动事件
    window.addEventListener('scroll', handleScroll);
}

/**
 * 评分星级初始化
 */
function initRatingStars() {
    const ratingElements = document.querySelectorAll('.rating');
    
    ratingElements.forEach(rating => {
        const ratingValue = parseFloat(rating.getAttribute('data-rating') || 0);
        const maxRating = 5;
        let starsHTML = '';
        
        // 生成评分星星
        for (let i = 1; i <= maxRating; i++) {
            if (i <= ratingValue) {
                // 整颗星
                starsHTML += '<i class="fas fa-star"></i>';
            } else if (i - 0.5 <= ratingValue) {
                // 半颗星
                starsHTML += '<i class="fas fa-star-half-alt"></i>';
            } else {
                // 空星
                starsHTML += '<i class="far fa-star"></i>';
            }
        }
        
        // 添加评分显示
        rating.innerHTML = starsHTML + `<span class="rating-value">${ratingValue.toFixed(1)}</span>`;
    });
}

/**
 * 移动端菜单初始化
 */
function initMobileMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (!menuToggle || !mobileMenu) return;
    
    menuToggle.addEventListener('click', function() {
        mobileMenu.classList.toggle('mobile-menu-open');
        menuToggle.classList.toggle('toggle-active');
    });
}

/**
 * 搜索功能
 */
function handleSearch(event) {
    event.preventDefault();
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;
    
    const searchTerm = searchInput.value.trim();
    if (searchTerm.length === 0) return;
    
    // 在实际项目中，这里可能会发送AJAX请求或跳转到搜索结果页
    console.log('搜索内容:', searchTerm);
    
    // 模拟搜索功能，跳转到搜索结果页
    window.location.href = `search.html?q=${encodeURIComponent(searchTerm)}`;
}

/**
 * 天气数据获取函数
 * 在实际项目中，这个函数会从服务器或第三方API获取数据
 */
function fetchWeatherData(location) {
    // 这是一个模拟函数，实际项目中会使用fetch或axios发送API请求
    console.log('获取天气数据:', location);
    
    // 返回模拟数据
    return new Promise((resolve) => {
        setTimeout(() => {
            resolve({
                location: location,
                current: {
                    temp: 25,
                    condition: '多云',
                    humidity: 65,
                    wind: '10km/h',
                    pressure: 1013,
                    feelsLike: 27,
                    sunrise: '06:12',
                    sunset: '18:45'
                },
                forecast: [
                    { day: '周一', high: 25, low: 17, condition: '多云', rainChance: 30 },
                    { day: '周二', high: 23, low: 15, condition: '晴', rainChance: 10 },
                    { day: '周三', high: 26, low: 18, condition: '晴', rainChance: 5 },
                    { day: '周四', high: 28, low: 20, condition: '晴', rainChance: 0 },
                    { day: '周五', high: 24, low: 16, condition: '小雨', rainChance: 40 }
                ]
            });
        }, 500);
    });
}

/**
 * 价格比较数据获取函数
 * 在实际项目中，这个函数会从服务器或第三方API获取数据
 */
function fetchPriceComparisonData(params) {
    // 这是一个模拟函数，实际项目中会使用fetch或axios发送API请求
    console.log('获取价格比较数据:', params);
    
    // 返回模拟数据
    return new Promise((resolve) => {
        setTimeout(() => {
            resolve({
                results: [
                    {
                        id: 1,
                        name: '东京希尔顿酒店',
                        thumbnail: 'images/hotel-1.jpg',
                        description: '位于市中心的豪华酒店，提供免费WiFi和室内游泳池',
                        rating: 4.5,
                        prices: [
                            { platform: '携程', price: 1200 },
                            { platform: '去哪儿', price: 1350 },
                            { platform: '飞猪', price: 1100 }
                        ],
                        priceHistory: [1150, 1200, 1250, 1100, 1050, 1100, 1200]
                    },
                    {
                        id: 2,
                        name: '东京丽思卡尔顿',
                        thumbnail: 'images/hotel-2.jpg',
                        description: '奢华5星级酒店，拥有壮观的城市景观和一流的服务',
                        rating: 5.0,
                        prices: [
                            { platform: '携程', price: 2200 },
                            { platform: '去哪儿', price: 2350 },
                            { platform: '飞猪', price: 2100 }
                        ],
                        priceHistory: [2250, 2200, 2300, 2200, 2150, 2100, 2200]
                    }
                ]
            });
        }, 800);
    });
}

/**
 * 旅行计划生成函数
 * 在实际项目中，这个函数会调用后端API或大模型API生成旅行计划
 */
function generateTravelPlan(params) {
    // 这是一个模拟函数，实际项目中会使用fetch或axios发送API请求
    console.log('生成旅行计划:', params);
    
    // 显示加载状态
    const planContainer = document.querySelector('.plan-container');
    if (planContainer) {
        planContainer.innerHTML = '<div class="loading">正在生成您的专属旅行计划...</div>';
    }
    
    // 返回模拟数据
    return new Promise((resolve) => {
        setTimeout(() => {
            resolve({
                destination: params.destination,
                duration: params.days,
                totalCost: 5800,
                highlights: ['浅草寺', '东京塔', '筑地市场'],
                itinerary: [
                    {
                        day: 1,
                        activities: [
                            {
                                time: '09:00',
                                name: '浅草寺',
                                description: '东京最古老的寺庙，建于公元645年',
                                image: 'images/asakusa.jpg',
                                ticketInfo: '免费参观，部分区域需要购票',
                                transport: { method: '地铁', duration: 20 }
                            },
                            {
                                time: '12:00',
                                name: '午餐 - 浅草寿司',
                                description: '品尝正宗的日本寿司',
                                image: 'images/sushi.jpg',
                                priceRange: '¥100-200/人',
                                transport: { method: '步行', duration: 5 }
                            },
                            {
                                time: '14:00',
                                name: '东京国立博物馆',
                                description: '日本最大的艺术博物馆，收藏了超过11万件展品',
                                image: 'images/museum.jpg',
                                ticketInfo: '成人票620日元',
                                transport: { method: '地铁', duration: 15 }
                            }
                        ]
                    },
                    {
                        day: 2,
                        activities: [
                            {
                                time: '10:00',
                                name: '东京塔',
                                description: '东京的标志性建筑，高333米',
                                image: 'images/tokyo-tower.jpg',
                                ticketInfo: '主甲板1200日元，顶层2000日元',
                                transport: { method: '地铁', duration: 25 }
                            }
                        ]
                    }
                ]
            });
        }, 2000);
    });
} 