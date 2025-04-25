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
        // Check if the application is configured to use a database
        $useDatabase = config('app.use_database', false);
        
        // If accessed via named route and no username provided
        if ($username === null) {
            // Check if user is authenticated
            if (Auth::check() && $useDatabase) {
                $user = Auth::user();
            } elseif (session()->has('mock_user')) {
                $user = session('mock_user');
            } else {
                // Redirect to login page if not logged in
                return redirect()->route('login')->with('error', 'Please login to view profile');
            }
        } else {
            // In database mode, find user by username
            if ($useDatabase) {
                $user = User::where('name', $username)->first();
            } else {
                $user = null;
            }
            
            // If user doesn't exist and in development environment, use mock data
            if (!$user) {
                $user = [
                    'id' => 1,
                    'name' => $username ?: 'Travel Expert',
                    'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg',
                    'bio' => 'Passionate about travel, with footprints in over 30 countries and regions. Specializes in sharing practical travel guides and beautiful memories from journeys.',
                    'location' => 'Hong Kong',
                    'education' => 'Hong Kong University',
                    'tags' => ['ENTP', 'Travel Blogger', 'Photography'],
                    'posts_count' => 24,
                    'followers_count' => 1280,
                    'following_count' => 325
                ];
            }
        }
        
        // Prepare post data
        if ($useDatabase && isset($user->id)) {
            // Get posts from database
            $pinnedPostIds = $user->pinned_posts ?? [];
            
            $pinnedPosts = Post::whereIn('id', $pinnedPostIds)->get();
            $regularPosts = Post::where('user_id', $user->id)
                                ->whereNotIn('id', $pinnedPostIds)
                                ->latest()
                                ->get();
                                
            $posts = $pinnedPosts->merge($regularPosts);
        } else {
            // Use mock data
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
        }
        
        // Determine if this is the user's own profile page
        $isOwner = false;
        if ($username) {
            $isOwner = (Auth::check() && Auth::user()->name === $username) || 
                       (session()->has('mock_user') && session('mock_user')['name'] === $username);
        } else {
            $isOwner = true;
        }
        
        return view('user.profile', [
            'user' => $user, 
            'posts' => $posts,
            'isOwner' => $isOwner
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
        
        return redirect()->route('user.favorites')->with('success', 'Profile updated successfully!');
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
        // Check if the application is configured to use a database
        $useDatabase = config('app.use_database', false);
        
        if ($useDatabase && Auth::check()) {
            $user = Auth::user();
            $favorites = $user->favorites()->with('user')->latest()->get();
        } else {
            // Use mock user or mock data
            if (session()->has('mock_user')) {
                $user = session('mock_user');
                
                // Ensure user object contains all necessary fields
                $defaultUser = [
                    'id' => 1,
                    'name' => $user['name'] ?? 'Travel Expert',
                    'email' => $user['email'] ?? 'travel@example.com',
                    'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg',
                    'bio' => 'Passionate about travel, with footprints in over 30 countries and regions.',
                    'location' => 'Hong Kong',
                    'education' => 'Hong Kong University',
                    'tags' => ['ENTP', 'Travel Blogger', 'Photography'],
                    'posts_count' => 24,
                    'followers_count' => 1280,
                    'following_count' => 325
                ];
                
                // Merge default values with session values
                $user = array_merge($defaultUser, $user);
                
                // Get favorite post IDs from session
                $favoriteIds = session()->get('user_favorites', []);
                
                // Mock favorites data based on IDs
                $allPosts = $this->getAllMockPosts();
                $favorites = [];
                
                foreach ($allPosts as $post) {
                    if (in_array($post['id'], $favoriteIds)) {
                        $favorites[] = $post;
                    }
                }
            } else {
                // If there's no authenticated user, redirect to login page
                return redirect()->route('login')->with('error', 'Please login to view favorites');
            }
        }
        
        return view('user.favorites', [
            'user' => $user, 
            'favorites' => $favorites,
            'isOwner' => true
        ]);
    }
    
    /**
     * Get all mock posts for favorites feature
     */
    private function getAllMockPosts()
    {
        return [
            [
                'id' => 1,
                'title' => 'Hokkaido 7-Day Trip',
                'cover_image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '7 Days 6 Nights · 12.24',
                'author' => [
                    'name' => 'Travel Expert',
                    'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg'
                ],
                'views' => 1245,
                'likes' => 324,
                'comments' => 56
            ],
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
            ],
            [
                'id' => 4,
                'title' => 'Yunnan Dali Erhai Lake Leisure Trip',
                'cover_image' => 'https://images.unsplash.com/photo-1555217851-6141ab127fa8?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '4 Days 3 Nights · Relaxing',
                'author' => [
                    'name' => 'Slow Traveler',
                    'avatar' => 'https://randomuser.me/api/portraits/women/56.jpg'
                ],
                'views' => 1879,
                'likes' => 402,
                'comments' => 67
            ],
            [
                'id' => 5,
                'title' => 'Tokyo Food Exploration',
                'cover_image' => 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '6 Days · 5k per person',
                'author' => [
                    'name' => 'Foodie Traveler',
                    'avatar' => 'https://randomuser.me/api/portraits/men/22.jpg'
                ],
                'views' => 4210,
                'likes' => 893,
                'comments' => 156
            ]
        ];
    }
    
    /**
     * Display user's own posts
     */
    public function myPosts()
    {
        $user = Auth::user();
        
        if (!$user && session()->has('mock_user')) {
            $user = session('mock_user');
        }
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to view your posts');
        }
        
        // Get user posts
        if (config('app.use_database', false)) {
            $posts = $user->posts()->latest()->get();
        } else {
            // Mock data for development
            $posts = [
                [
                    'id' => 1,
                    'title' => 'Hokkaido 7-Day Trip Guide',
                    'content' => 'This is a detailed guide for a 7-day trip to Hokkaido...',
                    'cover_image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'views' => 1245,
                    'likes' => 324,
                    'comments' => 12,
                    'created_at' => '2023-12-15'
                ],
                [
                    'id' => 4,
                    'title' => 'Best Street Food in Bangkok',
                    'content' => 'Discover the vibrant street food scene in Bangkok...',
                    'cover_image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'views' => 950,
                    'likes' => 218,
                    'comments' => 24,
                    'created_at' => '2023-11-30'
                ],
                [
                    'id' => 7,
                    'title' => 'Hidden Gems in Kyoto',
                    'content' => 'Explore the less-traveled paths in the ancient city of Kyoto...',
                    'cover_image' => 'https://images.unsplash.com/photo-1493780474015-ba834fd0ce2f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'views' => 780,
                    'likes' => 156,
                    'comments' => 18,
                    'created_at' => '2023-11-12'
                ]
            ];
        }
        
        return view('user.my', [
            'user' => $user,
            'posts' => $posts,
            'isOwner' => true
        ]);
    }
    
    /**
     * Show followers list
     */
    public function followers($username = null)
    {
        // Check if the application is configured to use a database
        $useDatabase = config('app.use_database', false);
        
        if ($useDatabase) {
            if ($username) {
                $user = User::where('name', $username)->first();
                if (!$user) {
                    abort(404, 'User does not exist');
                }
            } else {
                $user = Auth::user();
                if (!$user) {
                    return redirect()->route('login')->with('error', 'Please login to view followers');
                }
            }
            
            $followers = $user->followers()->get();
        } else {
            // Use mock data
            if ($username) {
                $user = [
                    'id' => 2,
                    'name' => $username,
                    'avatar' => 'https://randomuser.me/api/portraits/men/42.jpg',
                    'bio' => 'Travel enthusiast and photographer.',
                    'location' => 'Hong Kong',
                    'education' => 'HKU',
                    'tags' => ['Travel', 'Photography'],
                    'posts_count' => 15,
                    'followers_count' => 850,
                    'following_count' => 120
                ];
            } else if (session()->has('mock_user')) {
                $user = session('mock_user');
                
                // Ensure user object contains all necessary fields
                $defaultUser = [
                    'id' => 1,
                    'name' => $user['name'] ?? 'Travel Expert',
                    'email' => $user['email'] ?? 'travel@example.com',
                    'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg',
                    'bio' => 'Passionate about travel, with footprints in over 30 countries and regions.',
                    'location' => 'Hong Kong',
                    'education' => 'Hong Kong University',
                    'tags' => ['ENTP', 'Travel Blogger', 'Photography'],
                    'posts_count' => 24,
                    'followers_count' => 1280,
                    'following_count' => 325
                ];
                
                // Merge default values with session values
                $user = array_merge($defaultUser, $user);
            } else {
                return redirect()->route('login')->with('error', 'Please login to view followers');
            }
            
            // Mock follower data
            $followers = [
                [
                    'id' => 3,
                    'name' => 'Mountain Explorer',
                    'avatar' => 'https://randomuser.me/api/portraits/women/22.jpg',
                    'bio' => 'Love hiking and mountain views'
                ],
                [
                    'id' => 4,
                    'name' => 'City Walker',
                    'avatar' => 'https://randomuser.me/api/portraits/men/36.jpg',
                    'bio' => 'Urban explorer and street photographer'
                ]
            ];
        }
        
        // Determine if this is the user's own profile page
        $isOwner = false;
        if ($username) {
            $isOwner = (Auth::check() && Auth::user()->name === $username) || 
                       (session()->has('mock_user') && session('mock_user')['name'] === $username);
        } else {
            $isOwner = true;
        }
        
        return view('user.followers', [
            'user' => $user,
            'followers' => $followers,
            'isOwner' => $isOwner
        ]);
    }
    
    /**
     * Show following list
     */
    public function following($username = null)
    {
        // Check if the application is configured to use a database
        $useDatabase = config('app.use_database', false);
        
        if ($useDatabase) {
            if ($username) {
                $user = User::where('name', $username)->first();
                if (!$user) {
                    abort(404, 'User does not exist');
                }
            } else {
                $user = Auth::user();
                if (!$user) {
                    return redirect()->route('login')->with('error', 'Please login to view following');
                }
            }
            
            $following = $user->following()->get();
        } else {
            // Use mock data
            if ($username) {
                $user = [
                    'id' => 2,
                    'name' => $username,
                    'avatar' => 'https://randomuser.me/api/portraits/men/42.jpg',
                    'bio' => 'Travel enthusiast and photographer.',
                    'location' => 'Hong Kong',
                    'education' => 'HKU',
                    'tags' => ['Travel', 'Photography'],
                    'posts_count' => 15,
                    'followers_count' => 850,
                    'following_count' => 120
                ];
            } else if (session()->has('mock_user')) {
                $user = session('mock_user');
                
                // Ensure user object contains all necessary fields
                $defaultUser = [
                    'id' => 1,
                    'name' => $user['name'] ?? 'Travel Expert',
                    'email' => $user['email'] ?? 'travel@example.com',
                    'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg',
                    'bio' => 'Passionate about travel, with footprints in over 30 countries and regions.',
                    'location' => 'Hong Kong',
                    'education' => 'Hong Kong University',
                    'tags' => ['ENTP', 'Travel Blogger', 'Photography'],
                    'posts_count' => 24,
                    'followers_count' => 1280,
                    'following_count' => 325
                ];
                
                // Merge default values with session values
                $user = array_merge($defaultUser, $user);
            } else {
                return redirect()->route('login')->with('error', 'Please login to view following');
            }
            
            // Mock following data
            $following = [
                [
                    'id' => 5,
                    'name' => 'Food Traveller',
                    'avatar' => 'https://randomuser.me/api/portraits/women/68.jpg',
                    'bio' => 'Exploring the world through food'
                ],
                [
                    'id' => 6,
                    'name' => 'Adventure Seeker',
                    'avatar' => 'https://randomuser.me/api/portraits/men/71.jpg',
                    'bio' => 'Extreme sports and adventure travel'
                ]
            ];
        }
        
        // Determine if this is the user's own profile page
        $isOwner = false;
        if ($username) {
            $isOwner = (Auth::check() && Auth::user()->name === $username) || 
                       (session()->has('mock_user') && session('mock_user')['name'] === $username);
        } else {
            $isOwner = true;
        }
        
        return view('user.following', [
            'user' => $user,
            'following' => $following,
            'isOwner' => $isOwner
        ]);
    }
}