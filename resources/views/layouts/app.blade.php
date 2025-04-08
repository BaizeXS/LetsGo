<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LetsGO - 旅游笔记分享平台</title>
    <!-- 引入Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- 引入Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- 自定义CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @yield('styles')
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <!-- 顶部导航栏 -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- 搜索栏 -->
                <div class="relative w-full max-w-md">
                    <div class="flex items-center border border-gray-300 rounded-full overflow-hidden">
                        <i class="fas fa-search text-gray-400 ml-3"></i>
                        <input type="text" placeholder="搜索目的地或关键词" class="w-full py-2 px-3 outline-none">
                    </div>
                </div>
                
                <!-- 右侧图标 -->
                <div class="flex space-x-4 items-center">
                    <a href="#" class="text-yellow-500"><i class="fas fa-compass text-xl"></i></a>
                    <a href="#" class="text-gray-700"><i class="fas fa-bell text-xl"></i></a>
                    <a href="#" class="text-gray-700"><i class="fas fa-user-circle text-xl"></i></a>
                </div>
            </div>
        </div>
    </header>

    <!-- 主要内容 -->
    <main class="flex-grow container mx-auto px-4 py-6">
        @yield('content')
    </main>

    <!-- 底部导航栏 -->
    <footer class="bg-white border-t border-gray-200 sticky bottom-0 z-50">
        <div class="container mx-auto">
            <div class="flex justify-around items-center py-3">
                <a href="{{ route('home') }}" class="flex flex-col items-center text-red-500">
                    <i class="fas fa-home text-xl"></i>
                    <span class="text-xs mt-1">首页</span>
                </a>
                <a href="#" class="flex flex-col items-center text-gray-500 relative">
                    <div class="bg-red-500 rounded-full p-3 -mt-5 border-4 border-white">
                        <i class="fas fa-plus text-white text-xl"></i>
                    </div>
                    <span class="text-xs mt-1">发布</span>
                </a>
                <a href="#" class="flex flex-col items-center text-gray-500">
                    <i class="fas fa-user text-xl"></i>
                    <span class="text-xs mt-1">我的</span>
                </a>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html> 