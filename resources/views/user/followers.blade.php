@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Profile Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row items-center">
            <div class="w-32 h-32 flex-shrink-0 mb-4 md:mb-0">
                <img src="{{ $user['avatar'] }}" alt="{{ $user['name'] }}" class="w-full h-full rounded-full object-cover">
            </div>
            <div class="md:ml-8 text-center md:text-left flex-grow">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">{{ $user['name'] }}</h1>
                        <p class="text-gray-600 mt-1">{{ $user['bio'] }}</p>

                        <!-- Location and Education Info -->
                        @if(isset($user['location']) || isset($user['education']))
                        <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-gray-500">
                            @if(isset($user['location']))
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <span>{{ $user['location'] }}</span>
                            </div>
                            @endif
                            
                            @if(isset($user['education']))
                            <div class="flex items-center">
                                <i class="fas fa-graduation-cap mr-1"></i>
                                <span>{{ $user['education'] }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                        
                        <!-- User Tags -->
                        @if(isset($user['tags']) && count($user['tags']) > 0)
                        <div class="flex flex-wrap gap-2 mt-3">
                            @foreach($user['tags'] as $tag)
                            <span class="inline-block bg-gray-100 px-2 py-1 text-xs rounded-full text-gray-600">{{ $tag }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="mt-4 md:mt-0">
                        @if($isOwner)
                        <a href="{{ route('user.edit') }}" class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                            <i class="fas fa-edit mr-2"></i>Edit Profile
                        </a>
                        @else
                        <form action="{{ route('users.follow', $user['id']) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                <i class="fas fa-user-plus mr-2"></i>Follow
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                
                <div class="flex justify-center md:justify-start space-x-8 mt-6">
                    <a href="{{ $isOwner ? route('user.profile') : route('users.profile', $user['name']) }}" class="text-center">
                        <span class="block text-xl font-bold">{{ $user['posts_count'] }}</span>
                        <span class="text-gray-500">Posts</span>
                    </a>
                    <a href="{{ $isOwner ? route('user.followers') : route('users.followers', $user['name']) }}" class="text-center">
                        <span class="block text-xl font-bold">{{ $user['followers_count'] }}</span>
                        <span class="text-gray-500 border-b-2 border-blue-500 pb-1">Followers</span>
                    </a>
                    <a href="{{ $isOwner ? route('user.following') : route('users.following', $user['name']) }}" class="text-center">
                        <span class="block text-xl font-bold">{{ $user['following_count'] }}</span>
                        <span class="text-gray-500">Following</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Followers List -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-6">Followers List</h2>
        
        <div class="divide-y">
            @forelse($followers as $follower)
            <div class="py-4 flex items-center justify-between">
                <div class="flex items-center">
                    <img src="{{ $follower['avatar'] }}" alt="{{ $follower['name'] }}" class="w-12 h-12 rounded-full object-cover mr-4">
                    <div>
                        <h3 class="font-semibold">{{ $follower['name'] }}</h3>
                        <p class="text-sm text-gray-500">{{ $follower['bio'] }}</p>
                    </div>
                </div>
                
                @if($isOwner)
                <div>
                    <form action="{{ route('users.follow', $follower['id']) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200 transition">
                            @if($follower['is_following'])
                            Following
                            @else
                            Follow
                            @endif
                        </button>
                    </form>
                </div>
                @endif
            </div>
            @empty
            <div class="py-8 text-center">
                <p class="text-gray-500">No followers yet</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection 