@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">编辑个人资料</h1>
        
        <form action="{{ route('user.update') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-6">
            @csrf
            @method('PUT')
            
            <!-- Profile Avatar -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">头像</label>
                <div class="flex items-center">
                    <div class="mr-4">
                        <img src="{{ $user['avatar'] }}" alt="{{ $user['name'] }}" class="w-24 h-24 rounded-full object-cover">
                    </div>
                    <div>
                        <input type="file" name="avatar" id="avatar" class="border border-gray-300 p-2 w-full rounded">
                        <p class="text-sm text-gray-500 mt-1">推荐上传正方形图片，最大文件大小为 2MB</p>
                    </div>
                </div>
                @error('avatar')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">用户名</label>
                <input type="text" name="name" id="name" value="{{ $user['name'] }}" class="border border-gray-300 p-2 w-full rounded">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">邮箱</label>
                <input type="email" name="email" id="email" value="{{ $user['email'] }}" class="border border-gray-300 p-2 w-full rounded">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Location -->
            <div class="mb-4">
                <label for="location" class="block text-gray-700 text-sm font-bold mb-2">所在地</label>
                <input type="text" name="location" id="location" value="{{ $user['location'] ?? '' }}" class="border border-gray-300 p-2 w-full rounded">
                @error('location')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Education -->
            <div class="mb-4">
                <label for="education" class="block text-gray-700 text-sm font-bold mb-2">教育背景</label>
                <input type="text" name="education" id="education" value="{{ $user['education'] ?? '' }}" class="border border-gray-300 p-2 w-full rounded">
                @error('education')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Tags -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">个人标签</label>
                <div class="tags-input-container border border-gray-300 p-2 rounded flex flex-wrap gap-2">
                    @if(isset($user['tags']) && is_array($user['tags']))
                        @foreach($user['tags'] as $index => $tag)
                            <div class="tag-item bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm flex items-center">
                                <span>{{ $tag }}</span>
                                <input type="hidden" name="tags[]" value="{{ $tag }}">
                                <button type="button" class="ml-1 text-blue-500 hover:text-blue-700 remove-tag" data-index="{{ $index }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    @endif
                    <input type="text" id="tag-input" class="flex-grow outline-none" placeholder="输入标签后按回车添加">
                </div>
                <p class="text-sm text-gray-500 mt-1">最多添加 5 个标签，每个标签最多 50 个字符</p>
                @error('tags')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Bio -->
            <div class="mb-6">
                <label for="bio" class="block text-gray-700 text-sm font-bold mb-2">个人简介</label>
                <textarea name="bio" id="bio" rows="4" class="border border-gray-300 p-2 w-full rounded">{{ $user['bio'] }}</textarea>
                <p class="text-sm text-gray-500 mt-1">最多 1000 个字符</p>
                @error('bio')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Submit Button -->
            <div class="flex items-center justify-between">
                <a href="{{ route('user.profile') }}" class="text-blue-500 hover:underline">取消</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    保存修改
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tagInput = document.getElementById('tag-input');
        const tagsContainer = document.querySelector('.tags-input-container');
        
        // Add new tag
        tagInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && this.value.trim() !== '') {
                e.preventDefault();
                
                // Check if we already have 5 tags
                const existingTags = document.querySelectorAll('.tag-item');
                if (existingTags.length >= 5) {
                    alert('最多只能添加 5 个标签');
                    return;
                }
                
                // Create new tag
                const tagValue = this.value.trim();
                const tagItem = document.createElement('div');
                tagItem.className = 'tag-item bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm flex items-center';
                tagItem.innerHTML = `
                    <span>${tagValue}</span>
                    <input type="hidden" name="tags[]" value="${tagValue}">
                    <button type="button" class="ml-1 text-blue-500 hover:text-blue-700 remove-tag">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                // Insert before the input
                tagsContainer.insertBefore(tagItem, tagInput);
                
                // Clear input
                this.value = '';
            }
        });
        
        // Remove tag
        tagsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-tag')) {
                e.target.closest('.tag-item').remove();
            }
        });
    });
</script>
@endpush
@endsection 