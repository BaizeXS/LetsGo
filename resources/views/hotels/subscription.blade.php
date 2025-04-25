<!-- resources/views/hotels/subscription.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">酒店订阅</h1>

    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <h2 class="text-lg font-semibold mb-3">设置价格提醒</h2>
        <form id="subscription-form" class="space-y-4">
            <div>
                <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">目的地</label>
                <input type="text" id="destination" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="城市、地区或具体酒店名称">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="check-in" class="block text-sm font-medium text-gray-700 mb-1">入住日期</label>
                    <input type="date" id="check-in" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="check-out" class="block text-sm font-medium text-gray-700 mb-1">退房日期</label>
                    <input type="date" id="check-out" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="price-threshold" class="block text-sm font-medium text-gray-700 mb-1">价格阈值（低于此价格时通知我）</label>
                <input type="number" id="price-threshold" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="例如：500">
            </div>

            <div>
                <label for="email-notification" class="block text-sm font-medium text-gray-700 mb-1">通知方式</label>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <input type="checkbox" id="email-notification" class="mr-2">
                        <label for="email-notification">邮件通知</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="app-notification" class="mr-2">
                        <label for="app-notification">应用内通知</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 focus:outline-none">
                创建价格提醒
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4">
        <h2 class="text-lg font-semibold mb-3">我的价格提醒</h2>
        <div id="subscription-list" class="space-y-4">
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-medium">东京新大谷酒店</h3>
                        <p class="text-sm text-gray-600">2024-06-15 至 2024-06-20</p>
                        <p class="text-sm text-gray-600">价格阈值：¥1200/晚</p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="text-blue-500 hover:text-blue-700">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="mt-2 flex justify-between items-center">
                    <span class="text-sm text-green-600">
                        <i class="fas fa-bell"></i> 当前最低价：¥1350
                    </span>
                    <a href="#" class="text-sm text-blue-500 hover:underline">查看详情</a>
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-medium">巴黎香榭丽舍大道酒店</h3>
                        <p class="text-sm text-gray-600">2024-07-10 至 2024-07-15</p>
                        <p class="text-sm text-gray-600">价格阈值：¥1800/晚</p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="text-blue-500 hover:text-blue-700">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="mt-2 flex justify-between items-center">
                    <span class="text-sm text-yellow-600">
                        <i class="fas fa-exclamation-circle"></i> 价格接近阈值：¥1850
                    </span>
                    <a href="#" class="text-sm text-blue-500 hover:underline">查看详情</a>
                </div>
            </div>
        </div>
        <div class="text-center py-4" id="empty-state" style="display: none;">
            <p class="text-gray-500">您还没有创建任何价格提醒</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const subscriptionForm = document.getElementById('subscription-form');

        subscriptionForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // 获取表单数据
            const destination = document.getElementById('destination').value;
            const checkIn = document.getElementById('check-in').value;
            const checkOut = document.getElementById('check-out').value;
            const priceThreshold = document.getElementById('price-threshold').value;

            // 这里只是演示，实际应该发送到后端API
            alert(`已创建价格提醒：${destination}, ${checkIn} 至 ${checkOut}, 价格阈值 ¥${priceThreshold}`);

            // 清空表单
            subscriptionForm.reset();
        });
    });
</script>
@endsection