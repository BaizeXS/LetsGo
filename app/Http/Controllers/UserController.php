<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display user profile
     */
    public function profile(Request $request, $username = null)
    {
        if ($username) {
            $user = User::where('name', $username)->firstOrFail();
        } else {
            $user = Auth::user();
        }
        
        // In a real application, this should get from the database
        // For development purposes, we're using a mock data
        if (config('app.env') === 'local' && !$user) {
            $user = [
                'id' => 1,
                'name' => 'Travel Expert',
                'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg',
                'bio' => 'Passionate about travel, with footprints in over 30 countries and regions. Specializes in sharing practical travel guides and beautiful memories from journeys.',
                'location' => 'Hong Kong',
                'education' => 'Hong Kong University',
                'tags' => ['ENTP', 'Travel Blogger', 'Photography'],
                'posts_count' => 24,
                'followers_count' => 1280,
                'following_count' => 325
            ];
            
            // Get user's posts
            $posts = [
                [
                    'id' => 1,
                    'title' => 'Hokkaido 7-Day Trip',
                    'cover_image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'duration' => '7 Days 6 Nights · 12.24',
                    'views' => 1245,
                    'likes' => 324,
                    'comments' => 56,
                    'pinned' => true
                ],
                [
                    'id' => 5,
                    'title' => 'Tokyo Food Exploration',
                    'cover_image' => 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'duration' => '6 Days · 5k per person',
                    'views' => 4210,
                    'likes' => 893,
                    'comments' => 156,
                    'pinned' => false
                ]
            ];
        } else {
            // Get real user data
            // Get posts with pinned ones first
            $pinnedPostIds = $user->pinned_posts ?? [];
            
            $pinnedPosts = Post::whereIn('id', $pinnedPostIds)->get();
            $regularPosts = Post::where('user_id', $user->id)
                                ->whereNotIn('id', $pinnedPostIds)
                                ->latest()
                                ->get();
                                
            $posts = $pinnedPosts->merge($regularPosts);
        }
        
        return view('user.profile', [
            'user' => $user, 
            'posts' => $posts,
            'isOwner' => $username ? (Auth::check() && Auth::user()->name === $username) : true
        ]);
    }
    
    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = Auth::user();
        
        // In a real application, this should get from the database
        // For development purposes, we're using a mock data
        if (config('app.env') === 'local' && !$user) {
            $user = [
                'id' => 1,
                'name' => 'Travel Expert',
                'email' => 'travel@example.com',
                'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg',
                'bio' => 'Passionate about travel, with footprints in over 30 countries and regions. Specializes in sharing practical travel guides and beautiful memories from journeys.',
                'location' => 'Hong Kong',
                'education' => 'Hong Kong University',
                'tags' => ['ENTP', 'Travel Blogger', 'Photography']
            ];
        }
        
        return view('user.edit', ['user' => $user]);
    }
    
    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'avatar' => 'nullable|image|max:2048',
            'bio' => 'nullable|max:1000',
            'location' => 'nullable|max:255',
            'education' => 'nullable|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);
        
        $user = Auth::user();
        
        // Process avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::exists($user->avatar)) {
                Storage::delete($user->avatar);
            }
            
            $avatar = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = Storage::url($avatar);
        }
        
        // Process tags
        if ($request->has('tags')) {
            $validated['tags'] = array_filter($request->tags);
        }
        
        // Update user information
        $user->update($validated);
        
        return redirect()->route('user.profile')->with('success', 'Profile updated successfully!');
    }
    
    /**
     * Toggle pin post to profile
     */
    public function togglePinPost(Request $request, $postId)
    {
        $user = Auth::user();
        $pinnedPosts = $user->pinned_posts ?? [];
        
        if (in_array($postId, $pinnedPosts)) {
            // Remove from pinned posts
            $pinnedPosts = array_diff($pinnedPosts, [$postId]);
            $message = 'Post unpinned successfully';
        } else {
            // Add to pinned posts (limit to 3)
            if (count($pinnedPosts) >= 3) {
                return redirect()->back()->with('error', 'You can only pin up to 3 posts');
            }
            $pinnedPosts[] = $postId;
            $message = 'Post pinned successfully';
        }
        
        $user->update(['pinned_posts' => $pinnedPosts]);
        
        return redirect()->back()->with('success', $message);
    }
    
    /**
     * Toggle follow a user
     */
    public function toggleFollow(Request $request, $userId)
    {
        $user = Auth::user();
        $userToFollow = User::findOrFail($userId);
        
        if ($user->following()->where('following_id', $userId)->exists()) {
            $user->following()->detach($userId);
            $message = 'You have unfollowed ' . $userToFollow->name;
        } else {
            $user->following()->attach($userId);
            $message = 'You are now following ' . $userToFollow->name;
        }
        
        return redirect()->back()->with('success', $message);
    }
    
    /**
     * Display user's favorite posts
     */
    public function favorites()
    {
        $user = Auth::user();
        
        // In a real application, this should get from the database
        // For development purposes, we're using a mock data
        if (config('app.env') === 'local' && !$user) {
            $user = [
                'id' => 1,
                'name' => 'Travel Expert',
                'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg',
                'bio' => 'Passionate about travel, with footprints in over 30 countries and regions. Specializes in sharing practical travel guides and beautiful memories from journeys.',
                'location' => 'Hong Kong',
                'education' => 'Hong Kong University',
                'tags' => ['ENTP', 'Travel Blogger', 'Photography'],
                'posts_count' => 24,
                'followers_count' => 1280,
                'following_count' => 325
            ];
            
            // Get user's favorite posts
            $favorites = [
                [
                    'id' => 2,
                    'title' => 'Xinjiang Duku Highway',
                    'cover_image' => 'https://images.unsplash.com/photo-1494783367193-149034c05e8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'duration' => '5 Days · 4k per person',
                    'author' => [
                        'name' => 'Wind Chaser',
                        'avatar' => 'https://randomuser.me/api/portraits/men/32.jpg'
                    ],
                    'views' => 2341,
                    'likes' => 521,
                    'comments' => 89
                ],
                [
                    'id' => 3,
                    'title' => 'Paris Museum Tour',
                    'cover_image' => 'https://images.unsplash.com/photo-1431274172761-fca41d930114?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'duration' => '10 Days · Art Journey',
                    'author' => [
                        'name' => 'Art Enthusiast',
                        'avatar' => 'https://randomuser.me/api/portraits/women/28.jpg'
                    ],
                    'views' => 3450,
                    'likes' => 728,
                    'comments' => 134
                ]
            ];
        } else {
            // Get real user favorites data
            $favorites = $user->favorites()->with('user')->latest()->get();
        }
        
        return view('user.favorites', [
            'user' => $user, 
            'favorites' => $favorites,
            'isOwner' => true
        ]);
    }
    
    /**
     * Show followers list
     */
    public function followers($username = null)
    {
        if ($username) {
            $user = User::where('name', $username)->firstOrFail();
        } else {
            $user = Auth::user();
        }
        
        $followers = $user->followers()->get();
        
        return view('user.followers', [
            'user' => $user,
            'followers' => $followers,
            'isOwner' => $username ? (Auth::check() && Auth::user()->name === $username) : true
        ]);
    }
    
    /**
     * Show following list
     */
    public function following($username = null)
    {
        if ($username) {
            $user = User::where('name', $username)->firstOrFail();
        } else {
            $user = Auth::user();
        }
        
        $following = $user->following()->get();
        
        return view('user.following', [
            'user' => $user,
            'following' => $following,
            'isOwner' => $username ? (Auth::check() && Auth::user()->name === $username) : true
        ]);
    }
}