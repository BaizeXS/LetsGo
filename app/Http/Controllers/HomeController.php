<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the home page
     */
    public function index(Request $request)
    {
        // Get current category
        $activeCategory = $request->query('category', 'recommended');
        
        // Mock category data
        $categories = [
            ['name' => 'Recommended', 'slug' => 'recommended'],
            ['name' => 'Latest', 'slug' => 'latest'],
            ['name' => 'Popular', 'slug' => 'popular'],
            ['name' => 'Domestic', 'slug' => 'domestic'],
            ['name' => 'Overseas', 'slug' => 'overseas'],
            ['name' => 'Nearby', 'slug' => 'nearby'],
            ['name' => 'Special', 'slug' => 'special'],
            ['name' => 'Independent', 'slug' => 'independent'],
            ['name' => 'Homestay', 'slug' => 'homestay'],
        ];
        
        // Get posts based on category
        $posts = $this->getPostsByCategory($activeCategory);
        
        // Get user favorites
        $userFavorites = [];
        if (auth()->check()) {
            $userFavorites = auth()->user()->favorites()->pluck('post_id')->toArray();
        } elseif (session()->has('mock_user')) {
            $userFavorites = session()->get('user_favorites', []);
        }
        
        // Mark favorites
        foreach ($posts as &$post) {
            $post['is_favorite'] = in_array($post['id'], $userFavorites);
        }
        
        return view('home.index', [
            'activeCategory' => $activeCategory,
            'categories' => $categories,
            'posts' => $posts
        ]);
    }
    
    /**
     * Search posts by query
     */
    public function search(Request $request)
    {
        // Get search query
        $query = $request->input('query');
        
        // If no query is provided, redirect to home
        if (empty($query)) {
            return redirect()->route('home');
        }
        
        // Get posts that match the query
        $posts = Post::where(function($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
              ->orWhere('content', 'like', "%{$query}%")
              ->orWhere('destination', 'like', "%{$query}%")
              ->orWhere('duration', 'like', "%{$query}%");
            
            // Search in JSON tags field
            $q->orWhereRaw("JSON_CONTAINS(LOWER(tags), LOWER('\"" . addslashes($query) . "\"'))");
        })
        ->with('user')
        ->get()
        ->map(function($post) {
            // Format post data for view
            return [
                'id' => $post->id,
                'title' => $post->title,
                'cover_image' => $post->cover_image,
                'duration' => $post->duration,
                'cost' => $post->cost ?? null,
                'user' => [
                    'name' => $post->user->name,
                    'avatar' => $post->user->profile_photo_url ?? 'https://randomuser.me/api/portraits/men/1.jpg'
                ],
                'views' => $post->views,
                'likes' => $post->likes,
                'comments' => $post->comments_count,
            ];
        })
        ->toArray();
        
        // Mock category data for search page
        $categories = [
            ['name' => 'Recommended', 'slug' => 'recommended'],
            ['name' => 'Latest', 'slug' => 'latest'],
            ['name' => 'Popular', 'slug' => 'popular'],
            ['name' => 'Domestic', 'slug' => 'domestic'],
            ['name' => 'Overseas', 'slug' => 'overseas'],
            ['name' => 'Nearby', 'slug' => 'nearby'],
            ['name' => 'Special', 'slug' => 'special'],
            ['name' => 'Independent', 'slug' => 'independent'],
            ['name' => 'Homestay', 'slug' => 'homestay'],
        ];
        
        return view('home.search', [
            'query' => $query,
            'posts' => $posts,
            'categories' => $categories,
            'activeCategory' => 'search-results'
        ]);
    }
    
    /**
     * Get posts by category
     */
    private function getPostsByCategory($category)
    {
        // Check if we should use the database
        if (config('app.use_database', false)) {
            // Get posts from database based on category
            $query = Post::with('user');
            
            switch ($category) {
                case 'latest':
                    $query->latest();
                    break;
                case 'popular':
                    $query->orderBy('views', 'desc');
                    break;
                case 'domestic':
                    $query->where('destination', 'like', '%China%');
                    break;
                case 'overseas':
                    $query->where('destination', 'not like', '%China%');
                    break;
                // Add more categories as needed
                default:
                    // Default sorting for recommended
                    $query->orderBy('likes', 'desc');
                    break;
            }
            
            // Get posts and format for view
            return $query->limit(12)->get()->map(function($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'cover_image' => $post->cover_image,
                    'duration' => $post->duration,
                    'cost' => $post->cost ?? null,
                    'user' => [
                        'name' => $post->user->name,
                        'avatar' => $post->user->profile_photo_url ?? 'https://randomuser.me/api/portraits/men/1.jpg'
                    ],
                    'views' => $post->views,
                    'likes' => $post->likes,
                    'comments' => $post->comments_count,
                ];
            })->toArray();
        }
        
        // Fallback to mock data
        return $this->getDummyPosts();
    }
    
    /**
     * Get mock post data
     */
    private function getDummyPosts()
    {
        // Get user favorites
        $userFavorites = [];
        if (auth()->check()) {
            $userFavorites = auth()->user()->favorites()->pluck('post_id')->toArray();
        } elseif (session()->has('mock_user')) {
            $userFavorites = session()->get('user_favorites', []);
        }
        
        $posts = [
            [
                'id' => 1,
                'title' => 'Hokkaido 7-Day Trip',
                'cover_image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '7 Days 6 Nights · 12.24',
                'user' => [
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
                'cost' => '4k per person',
                'user' => [
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
                'user' => [
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
                'cost' => '2.5k per person',
                'user' => [
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
                'user' => [
                    'name' => 'Foodie Traveler',
                    'avatar' => 'https://randomuser.me/api/portraits/men/22.jpg'
                ],
                'views' => 4210,
                'likes' => 893,
                'comments' => 156
            ],
            [
                'id' => 6,
                'title' => 'Chiang Mai Hidden Gems',
                'cover_image' => 'https://images.unsplash.com/photo-1528181304800-259b08848526?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '5 Days · Off the Beaten Path',
                'user' => [
                    'name' => 'Explorer',
                    'avatar' => 'https://randomuser.me/api/portraits/men/76.jpg'
                ],
                'views' => 1567,
                'likes' => 328,
                'comments' => 48
            ],
            [
                'id' => 7,
                'title' => 'Sanya Family Vacation Guide',
                'cover_image' => 'https://images.unsplash.com/photo-1540202404-a2f29016b523?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '6 Days 5 Nights · Family Trip',
                'cost' => '6k for family',
                'user' => [
                    'name' => 'Happy Mom',
                    'avatar' => 'https://randomuser.me/api/portraits/women/65.jpg'
                ],
                'views' => 3245,
                'likes' => 714,
                'comments' => 129
            ],
            [
                'id' => 8,
                'title' => 'Tibet Lhasa Pilgrimage',
                'cover_image' => 'https://images.unsplash.com/photo-1461823385004-d7660947a7c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '8 Days · Spiritual Journey',
                'user' => [
                    'name' => 'Plateau Traveler',
                    'avatar' => 'https://randomuser.me/api/portraits/men/42.jpg'
                ],
                'views' => 2789,
                'likes' => 651,
                'comments' => 94
            ]
        ];
        
        // Mark favorites
        foreach ($posts as &$post) {
            $post['is_favorite'] = in_array($post['id'], $userFavorites);
        }
        
        return $posts;
    }

    /**
     * Get posts by location
     */
    public function getPostsByLocation(Request $request)
    {
        $location = $request->input('location');
        
        if (empty($location)) {
            return response()->json(['error' => 'Location parameter is required'], 400);
        }
        
        // Get posts that match the location
        if (config('app.use_database', false)) {
            $posts = Post::where('destination', 'like', "%{$location}%")
                ->with('user')
                ->limit(10)
                ->get()
                ->map(function($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'destination' => $post->destination,
                        'cover_image' => $post->cover_image,
                        'views' => $post->views,
                        'likes' => $post->likes,
                    ];
                });
                
            return response()->json($posts);
        }
        
        // Mock data if database is not being used
        $allPosts = $this->getDummyPosts();
        
        // Filter posts based on location
        $filteredPosts = array_filter($allPosts, function($post) use ($location) {
            return stripos($post['title'], $location) !== false;
        });
        
        return response()->json(array_values($filteredPosts));
    }
} 