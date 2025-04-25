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
        $categories = $this->getCategories();
        
        $posts = [];
        $useMockData = $this->shouldUseMockData();
        
        if (!$useMockData) {
            try {
                // Database connection successful, get posts
                $posts = $this->getPostsFromDatabase($activeCategory);
            } catch (\Exception $e) {
                // Query failed, use mock data
                $useMockData = true;
            }
        }
        
        // If using mock data
        if ($useMockData) {
            $posts = $this->filterPostsByCategory($this->getDummyPosts(), $activeCategory);
        }
        
        // Mark user favorites
        $posts = $this->markUserFavorites($posts);
        
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
            return $this->formatPostForView($post);
        })
        ->toArray();
        
        return view('home.search', [
            'query' => $query,
            'posts' => $posts,
            'categories' => $this->getCategories(),
            'activeCategory' => 'search-results'
        ]);
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
        
        $posts = [];
        $useMockData = $this->shouldUseMockData();
        
        if (!$useMockData) {
            try {
                // Database connection successful, attempt query
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
                    })
                    ->toArray();
            } catch (\Exception $e) {
                // Query failed, use mock data
                $useMockData = true;
            }
        }
        
        // If using mock data
        if ($useMockData) {
            // Get all mock posts
            $allPosts = $this->getDummyPosts();
            
            // Filter posts based on location
            $filteredPosts = array_filter($allPosts, function($post) use ($location) {
                return stripos($post['title'], $location) !== false ||
                      (isset($post['destination']) && stripos($post['destination'], $location) !== false);
            });
            
            $posts = array_values($filteredPosts);
        }
                
        return response()->json($posts);
    }

    /**
     * API method for searching posts via AJAX
     */
    public function apiSearch(Request $request)
    {
        // Get search query
        $query = $request->input('query');

        // If no query is provided, return empty array
        if (empty($query)) {
            return response()->json(['posts' => []]);
        }

        $posts = [];
        $useMockData = $this->shouldUseMockData();

        if (!$useMockData) {
            try {
                // Database connection successful, attempt query
                // Use Post model's scopeSearch method
                $posts = Post::search($query)
                    ->with('user')
                    ->limit(8)
                    ->get()
                    ->map(function($post) use ($query) {
                        // Prepare excerpt content - try to find matching text around query
                        $excerpt = $this->generateExcerpt($post->content ?? '', $query);
                        
                        // Format post data for view
                        $formattedPost = $this->formatPostForView($post);
                        $formattedPost['excerpt'] = $excerpt;
                        
                        return $formattedPost;
                    })
                    ->toArray();
            } catch (\Exception $e) {
                // Query failed, use mock data
                $useMockData = true;
                \Log::error('Database search failed: ' . $e->getMessage());
            }
        }

        // If database connection or query failed, use mock data
        if ($useMockData) {
            // Get dummy posts
            $allPosts = $this->getDummyPosts();

            // Filter posts based on search term
            $filteredPosts = array_filter($allPosts, function($post) use ($query) {
                $query = strtolower($query);

                // Check if title contains query
                $titleMatch = strpos(strtolower($post['title']), $query) !== false;
                
                // Check if destination contains query
                $destinationMatch = isset($post['destination']) && 
                    strpos(strtolower($post['destination']), $query) !== false;
                
                // Check if content contains query
                $contentMatch = isset($post['content']) && 
                    strpos(strtolower($post['content']), $query) !== false;
                
                // Return true if any match found
                return $titleMatch || $destinationMatch || $contentMatch;
            });

            $posts = array_values($filteredPosts);

            // Add excerpts to mock data
            foreach ($posts as &$post) {
                // If no content, use title as part of content
                if (!isset($post['content'])) {
                    $post['content'] = "Travel notes about {$post['title']}. Amazing experience in {$post['destination']}.";
                }
                
                $post['excerpt'] = $this->generateExcerpt($post['content'], $query);
            }

            // Limit to 8 results
            $posts = array_slice($posts, 0, 8);
        }

        // Mark user favorites
        $posts = $this->markUserFavorites($posts);

        return response()->json(['posts' => $posts, 'query' => $query]);
    }
    
    /**
     * API method to get posts for infinite loading
     */
    public function getPosts(Request $request)
    {
        // Get params
        $page = $request->input('page', 1);
        $category = $request->input('category', 'recommended');
        $limit = $request->input('limit', 8);
        
        $posts = [];
        $useMockData = $this->shouldUseMockData();
        
        if (!$useMockData) {
            try {
                // Database connection successful, get posts
                $posts = $this->getPostsFromDatabase($category, $page, $limit);
            } catch (\Exception $e) {
                // Query failed, use mock data
                $useMockData = true;
            }
        }
        
        // If using mock data
        if ($useMockData) {
            // Get filtered posts by category
            $allPosts = $this->filterPostsByCategory($this->getDummyPosts(), $category);
            
            // Apply pagination
            $offset = ($page - 1) * $limit;
            $posts = array_slice($allPosts, $offset, $limit);
            
            // If no more posts, return empty array
            if (empty($posts)) {
                return response()->json(['posts' => []]);
            }
        }
        
        // Mark user favorites
        $posts = $this->markUserFavorites($posts);
        
        return response()->json(['posts' => $posts]);
    }
    
    /**
     * Get standard categories list
     */
    private function getCategories()
    {
        return [
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
    }
    
    /**
     * Check if mock data should be used
     */
    private function shouldUseMockData()
    {
        if (config('app.use_database', false)) {
            try {
                // Try to connect to database without executing a query
                \DB::connection()->getPdo();
                return false;
            } catch (\Exception $e) {
                // Connection failed, use mock data
                return true;
            }
        }
        return true;
    }
    
    /**
     * Format a post for view
     */
    private function formatPostForView($post)
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'cover_image' => $post->cover_image,
            'duration' => $post->duration,
            'cost' => $post->cost ?? null,
            'destination' => $post->destination ?? null,
            'user' => [
                'name' => $post->user->name,
                'avatar' => $post->user->profile_photo_url ?? 'https://randomuser.me/api/portraits/men/1.jpg'
            ],
            'views' => $post->views,
            'likes' => $post->likes,
            'comments' => $post->comments_count,
        ];
    }
    
    /**
     * Generate excerpt from content based on search query
     */
    private function generateExcerpt($content, $query)
    {
        if (empty($content)) {
            return '';
        }
        
        // Find first match position in content
        $pos = stripos($content, $query);
        if ($pos !== false) {
            // Get text snippet around match
            $start = max(0, $pos - 50);
            $length = strlen($query) + 100; // Query term + 50 chars before and after
            $excerpt = substr($content, $start, $length);
            
            // Add ellipsis if excerpt doesn't start at beginning
            if ($start > 0) {
                $excerpt = '...' . $excerpt;
            }
            
            // Add ellipsis if excerpt doesn't end at content end
            if ($start + $length < strlen($content)) {
                $excerpt .= '...';
            }
        } else {
            // If no match found, use beginning of content
            $excerpt = substr($content, 0, 100) . '...';
        }
        
        return $excerpt;
    }
    
    /**
     * Filter and sort posts by category
     */
    private function filterPostsByCategory($posts, $category)
    {
        switch ($category) {
            case 'latest':
                // Sort by creation date (assuming newer posts have higher IDs)
                usort($posts, function($a, $b) {
                    return $b['id'] - $a['id'];
                });
                break;
            case 'popular':
                // Sort by views
                usort($posts, function($a, $b) {
                    return $b['views'] - $a['views'];
                });
                break;
            case 'domestic':
                // Filter for domestic posts
                $posts = array_filter($posts, function($post) {
                    return stripos($post['title'], 'China') !== false || 
                           stripos($post['title'], 'Yunnan') !== false || 
                           stripos($post['title'], 'Xinjiang') !== false || 
                           stripos($post['title'], 'Beijing') !== false ||
                           stripos($post['title'], 'Tibet') !== false ||
                           stripos($post['title'], 'Sanya') !== false;
                });
                $posts = array_values($posts);
                break;
            case 'overseas':
                // Filter for international posts
                $posts = array_filter($posts, function($post) {
                    return stripos($post['title'], 'China') === false && 
                           stripos($post['title'], 'Yunnan') === false && 
                           stripos($post['title'], 'Xinjiang') === false && 
                           stripos($post['title'], 'Beijing') === false &&
                           stripos($post['title'], 'Tibet') === false &&
                           stripos($post['title'], 'Sanya') === false;
                });
                $posts = array_values($posts);
                break;
            default:
                // Default sorting for recommended (by likes)
                usort($posts, function($a, $b) {
                    return $b['likes'] - $a['likes'];
                });
                break;
        }
        
        return $posts;
    }
    
    /**
     * Mark user favorites in post list
     */
    private function markUserFavorites($posts)
    {
        // Get user favorites
        $userFavorites = [];
        if (auth()->check()) {
            try {
                $userFavorites = auth()->user()->favorites()->pluck('post_id')->toArray();
            } catch (\Exception $e) {
                // If getting favorites fails, ignore the error
                $userFavorites = [];
            }
        } elseif (session()->has('mock_user')) {
            $userFavorites = session()->get('user_favorites', []);
        }
        
        // Mark favorites
        foreach ($posts as &$post) {
            $post['is_favorite'] = in_array($post['id'], $userFavorites);
        }
        
        return $posts;
    }
    
    /**
     * Get posts from database with category and pagination
     */
    private function getPostsFromDatabase($category, $page = 1, $limit = 12)
    {
        // Create query builder
        $query = Post::with('user');
        
        // Apply different query conditions based on category
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
            default:
                // Recommended sorting
                $query->orderBy('likes', 'desc');
                break;
        }
        
        // Apply pagination
        $query->skip(($page - 1) * $limit)->take($limit);
        
        // Get and format data
        return $query->get()->map(function($post) {
            return $this->formatPostForView($post);
        })->toArray();
    }
    
    /**
     * Get mock post data
     */
    private function getDummyPosts()
    {
        $posts = [
            [
                'id' => 1,
                'title' => 'Hokkaido 7-Day Trip',
                'cover_image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '7 Days 6 Nights · 12.24',
                'destination' => 'Hokkaido, Japan',
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
                'destination' => 'Xinjiang, China',
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
                'destination' => 'Paris, France',
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
                'cover_image' => 'https://images.unsplash.com/photo-1558005137-d9619a5c539f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '4 Days 3 Nights · Relaxing',
                'cost' => '2.5k per person',
                'destination' => 'Yunnan, China',
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
                'destination' => 'Tokyo, Japan',
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
                'destination' => 'Chiang Mai, Thailand',
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
                'destination' => 'Sanya, China',
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
                'destination' => 'Tibet, China',
                'user' => [
                    'name' => 'Plateau Traveler',
                    'avatar' => 'https://randomuser.me/api/portraits/men/42.jpg'
                ],
                'views' => 2789,
                'likes' => 651,
                'comments' => 94
            ]
        ];
        
        return $posts;
    }
} 