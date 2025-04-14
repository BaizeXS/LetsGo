<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LetsGO - Travel Notes Sharing Platform</title>
    <!-- 引入Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- 引入Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- 引入腾讯地图API -->
    <script charset="utf-8" src="https://map.qq.com/api/js?v=2.exp&key={{ env('TENCENT_MAP_KEY', '') }}"></script>
    <!-- 自定义CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- 在head部分添加meta标签，用于CSRF保护 -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('styles')
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <!-- 顶部导航栏 -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- 搜索栏 -->
                <div class="relative w-full max-w-md">
                    <form action="{{ route('search') }}" method="GET">
                        <div class="flex items-center border border-gray-300 rounded-full overflow-hidden">
                            <button type="submit" class="px-3">
                                <i class="fas fa-search text-gray-400"></i>
                            </button>
                            <input type="text" name="query" placeholder="Search destinations or keywords" class="w-full py-2 px-1 outline-none" value="{{ request('query') }}">
                        </div>
                    </form>
                </div>
                
                <!-- 右侧图标 -->
                <div class="flex space-x-4 items-center">
                    <button id="open-map-btn" class="text-yellow-500"><i class="fas fa-compass text-xl"></i></button>
                    
                    @if(isset($isAuthenticated) && $isAuthenticated)
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center focus:outline-none">
                                <div class="flex items-center gap-2">
                                    <div class="text-sm text-gray-800">{{ $authUser['email'] }}</div>
                                    <i class="fas fa-user-circle text-xl"></i>
                                </div>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                <a href="{{ url('/favorites') }}" class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 border-b border-gray-100">
                                    My
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-3 text-sm text-gray-800 hover:bg-gray-100">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 text-sm hover:text-red-500">Log In</a>
                        <a href="{{ route('register') }}" class="bg-red-500 text-white text-sm px-4 py-2 rounded-full hover:bg-red-600">Sign Up</a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Map Modal -->
    <div id="map-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg w-11/12 md:w-3/4 lg:w-2/3 max-w-4xl h-3/4 flex flex-col">
            <div class="flex justify-between items-center p-4 border-b">
                <h2 class="text-xl font-semibold text-gray-800">Explore Destinations</h2>
                <button id="close-map-btn" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-4 flex-grow flex flex-col md:flex-row">
                <div class="flex flex-col w-full md:w-3/5 h-full">
                    <div class="flex mb-4">
                        <div class="relative w-full">
                            <input id="map-search" type="text" placeholder="Search for a destination" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            <button id="search-map-btn" class="absolute right-2 top-2 text-gray-500">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div id="tencent-map" class="w-full flex-grow rounded-lg border border-gray-300"></div>
                </div>
                
                <!-- Posts display panel -->
                <div id="map-posts-panel" class="w-full md:w-2/5 h-full md:ml-4 mt-4 md:mt-0 overflow-hidden flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="map-posts-title" class="text-lg font-semibold text-gray-800">Travel Posts</h3>
                        <span id="map-posts-count" class="text-sm text-gray-500">0 posts found</span>
                    </div>
                    <div id="map-posts-container" class="flex-grow overflow-y-auto border border-gray-200 rounded-lg p-2">
                        <div id="map-posts-placeholder" class="flex flex-col items-center justify-center h-full text-gray-500">
                            <i class="fas fa-map-marker-alt text-4xl mb-2"></i>
                            <p>Click on a marker and select "Show travel posts" to view related travel notes</p>
                        </div>
                        <div id="map-posts-list" class="hidden space-y-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 主要内容 -->
    <main class="flex-grow container mx-auto px-4 py-6">
        @yield('content')
    </main>

    @if(config('app.env') === 'local')
    <!-- 调试信息区域 - 仅在开发环境中显示 -->
    <div class="bg-gray-100 border-t border-gray-200 py-2 text-xs text-gray-500 text-center">
        认证状态: @if(isset($isAuthenticated) && $isAuthenticated) 已登录 ({{ $authUser['email'] }}) @else 未登录 @endif | 
        当前路由: {{ Route::currentRouteName() }}
    </div>
    @endif

    <!-- 底部导航栏 -->
    <footer class="bg-white border-t border-gray-200 sticky bottom-0 z-50">
        <div class="container mx-auto">
            <div class="flex justify-around items-center py-3">
                <a href="{{ route('home') }}" class="flex flex-col items-center text-red-500">
                    <i class="fas fa-home text-xl"></i>
                    <span class="text-xs mt-1">Home</span>
                </a>
                
                @if(isset($isAuthenticated) && $isAuthenticated)
                    <a href="{{ route('posts.create') }}" class="flex flex-col items-center text-gray-500 relative">
                        <div class="bg-red-500 rounded-full p-3 -mt-5 border-4 border-white">
                            <i class="fas fa-plus text-white text-xl"></i>
                        </div>
                        <span class="text-xs mt-1">Post</span>
                    </a>
                    <a href="{{ url('/favorites') }}" class="flex flex-col items-center text-gray-500">
                        <i class="fas fa-user text-xl"></i>
                        <span class="text-xs mt-1">My</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex flex-col items-center text-gray-500 relative">
                        <div class="bg-red-500 rounded-full p-3 -mt-5 border-4 border-white">
                            <i class="fas fa-plus text-white text-xl"></i>
                        </div>
                        <span class="text-xs mt-1">Post</span>
                    </a>
                    <a href="{{ route('login') }}" class="flex flex-col items-center text-gray-500">
                        <i class="fas fa-user text-xl"></i>
                        <span class="text-xs mt-1">Login</span>
                    </a>
                @endif
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        // Map functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mapModal = document.getElementById('map-modal');
            const openMapBtn = document.getElementById('open-map-btn');
            const closeMapBtn = document.getElementById('close-map-btn');
            const mapElement = document.getElementById('tencent-map');
            const searchInput = document.getElementById('map-search');
            const searchBtn = document.getElementById('search-map-btn');
            
            let map;
            let markers = [];
            let searchService;
            let geocoder;
            
            // Initialize map
            function initMap() {
                // 默认中心位置 - 北京
                const defaultCenter = new qq.maps.LatLng(39.9042, 116.4074);
                
                // 创建地图实例
                map = new qq.maps.Map(mapElement, {
                    center: defaultCenter,
                    zoom: 11,
                    mapTypeControl: true,
                    panControl: true,
                    zoomControl: true,
                    scaleControl: true
                });
                
                // 创建地点搜索服务
                searchService = new qq.maps.SearchService({
                    complete: function(results) {
                        // 搜索完成回调
                        if (results && results.detail.pois.length > 0) {
                            const place = results.detail.pois[0];
                            const location = {
                                name: place.name,
                                lat: place.latLng.lat,
                                lng: place.latLng.lng
                            };
                            
                            // 清除之前的标记
                            clearMarkers();
                            
                            // 移动地图到搜索结果位置
                            map.setCenter(new qq.maps.LatLng(location.lat, location.lng));
                            map.setZoom(14);
                            
                            // 添加新标记
                            addMarker(location);
                        }
                    }
                });
                
                // 创建地理编码服务，用于坐标和地址转换
                geocoder = new qq.maps.Geocoder();
                
                // 添加默认的热门旅游地标记
                addDefaultMarkers();
            }
            
            // 添加热门旅游目的地标记
            function addDefaultMarkers() {
                const popularDestinations = [
                    { name: "北京故宫", lat: 39.9163, lng: 116.3972 },
                    { name: "上海外滩", lat: 31.2304, lng: 121.4912 },
                    { name: "西安兵马俑", lat: 34.3841, lng: 109.2785 },
                    { name: "广州塔", lat: 23.1066, lng: 113.3214 },
                    { name: "深圳世界之窗", lat: 22.5347, lng: 113.9740 },
                    { name: "杭州西湖", lat: 30.2590, lng: 120.1388 }
                ];
                
                popularDestinations.forEach(destination => {
                    addMarker(destination);
                });
            }
            
            // 添加标记到地图
            function addMarker(location) {
                const position = new qq.maps.LatLng(location.lat, location.lng);
                
                // 创建标记
                const marker = new qq.maps.Marker({
                    position: position,
                    map: map,
                    title: location.name
                });
                
                // 创建信息窗口
                const info = new qq.maps.InfoWindow({
                    map: map
                });
                
                // 设置信息窗口内容
                const infoContent = document.createElement('div');
                infoContent.innerHTML = `
                    <div class="p-2" style="width: 200px;">
                        <h3 style="font-weight: bold; margin-bottom: 5px;">${location.name}</h3>
                        <p style="font-size: 12px; margin-bottom: 5px;">Explore travel notes about this destination</p>
                        <a href="/search?query=${encodeURIComponent(location.name)}" style="color: #3b82f6; font-size: 12px; display: block; margin-top: 5px;">View related posts</a>
                        <button id="load-posts-${location.lat}-${location.lng}" style="color: #eab308; font-size: 12px; display: block; margin-top: 5px; cursor: pointer; background: none; border: none; padding: 0; text-align: left;">Show travel posts</button>
                    </div>
                `;
                
                // 添加标记点击事件
                qq.maps.event.addListener(marker, 'click', function() {
                    info.open();
                    info.setContent(infoContent);
                    info.setPosition(position);
                    
                    // 添加显示旅行笔记按钮点击事件
                    setTimeout(() => {
                        const loadPostsBtn = document.getElementById(`load-posts-${location.lat}-${location.lng}`);
                        if (loadPostsBtn) {
                            loadPostsBtn.addEventListener('click', () => {
                                loadPostsForLocation(location.name);
                            });
                        }
                    }, 100);
                });
                
                markers.push(marker);
                return marker;
            }
            
            // 清除所有标记
            function clearMarkers() {
                markers.forEach(marker => {
                    marker.setMap(null);
                });
                markers = [];
            }
            
            // 搜索地点
            function searchPlace(query) {
                // 使用腾讯地图搜索服务
                searchService.search(query);
            }
            
            // 加载指定位置的旅行笔记
            function loadPostsForLocation(locationName) {
                const mapPostsTitle = document.getElementById('map-posts-title');
                const mapPostsCount = document.getElementById('map-posts-count');
                const mapPostsPlaceholder = document.getElementById('map-posts-placeholder');
                const mapPostsList = document.getElementById('map-posts-list');
                
                // 更新标题
                mapPostsTitle.textContent = `Travel Posts for ${locationName}`;
                
                // 显示加载状态
                mapPostsPlaceholder.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full">
                        <i class="fas fa-spinner fa-spin text-yellow-500 text-4xl mb-2"></i>
                        <p>Loading travel posts for ${locationName}...</p>
                    </div>
                `;
                mapPostsPlaceholder.classList.remove('hidden');
                mapPostsList.classList.add('hidden');
                
                // 获取该位置的旅行笔记
                fetch(`/api/posts/location?location=${encodeURIComponent(locationName)}`)
                    .then(response => response.json())
                    .then(posts => {
                        // 更新笔记数量
                        mapPostsCount.textContent = `${posts.length} posts found`;
                        
                        if (posts.length === 0) {
                            // 显示无笔记消息
                            mapPostsPlaceholder.innerHTML = `
                                <div class="flex flex-col items-center justify-center h-full text-gray-500">
                                    <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                                    <p>No travel posts found for ${locationName}</p>
                                    <a href="/search?query=${encodeURIComponent(locationName)}" class="text-yellow-500 mt-2">Search all posts</a>
                                </div>
                            `;
                            mapPostsPlaceholder.classList.remove('hidden');
                            mapPostsList.classList.add('hidden');
                        } else {
                            // 生成笔记列表HTML
                            mapPostsList.innerHTML = posts.map(post => `
                                <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                                    <a href="/posts/${post.id}" class="block">
                                        <div class="relative pb-48">
                                            <img src="${post.cover_image}" class="absolute inset-0 h-full w-full object-cover" alt="${post.title}">
                                        </div>
                                        <div class="p-3">
                                            <h4 class="font-medium text-gray-900 mb-1 truncate">${post.title}</h4>
                                            <div class="flex justify-between text-xs text-gray-500">
                                                <span><i class="far fa-eye mr-1"></i>${post.views}</span>
                                                <span><i class="far fa-heart mr-1"></i>${post.likes}</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            `).join('');
                            
                            mapPostsPlaceholder.classList.add('hidden');
                            mapPostsList.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching posts:', error);
                        mapPostsPlaceholder.innerHTML = `
                            <div class="flex flex-col items-center justify-center h-full text-gray-500">
                                <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-2"></i>
                                <p>Error loading posts. Please try again later.</p>
                            </div>
                        `;
                        mapPostsPlaceholder.classList.remove('hidden');
                        mapPostsList.classList.add('hidden');
                    });
            }
            
            // 打开地图模态框
            openMapBtn.addEventListener('click', function() {
                mapModal.classList.remove('hidden');
                // 如果地图尚未初始化，则初始化
                if (!map) {
                    initMap();
                }
            });
            
            // 关闭地图模态框
            closeMapBtn.addEventListener('click', function() {
                mapModal.classList.add('hidden');
            });
            
            // 点击模态框外部区域关闭
            mapModal.addEventListener('click', function(e) {
                if (e.target === mapModal) {
                    mapModal.classList.add('hidden');
                }
            });
            
            // 处理搜索按钮点击
            searchBtn.addEventListener('click', function() {
                const query = searchInput.value.trim();
                if (query) {
                    searchPlace(query);
                }
            });
            
            // 处理搜索框回车键
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = searchInput.value.trim();
                    if (query) {
                        searchPlace(query);
                    }
                }
            });
        });
    </script>
    @yield('scripts')
</body>
</html> 