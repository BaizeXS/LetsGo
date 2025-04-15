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
        
        $posts = [];
        $useMockData = true;
        
        // 检查数据库连接和配置
        if (config('app.use_database', false)) {
            try {
                // 尝试连接数据库但不执行查询
                \DB::connection()->getPdo();
                $useMockData = false;
            } catch (\Exception $e) {
                // 连接失败，使用模拟数据
                $useMockData = true;
            }
        }
        
        if (!$useMockData) {
            try {
                // 数据库连接成功，获取帖子
                $posts = $this->getPostsFromDatabase($activeCategory);
            } catch (\Exception $e) {
                // 查询失败，使用模拟数据
                $useMockData = true;
            }
        }
        
        // 如果使用模拟数据
        if ($useMockData) {
            // 获取所有模拟帖子
            $allPosts = $this->getDummyPosts();
            
            // 应用分类过滤
            switch ($activeCategory) {
                case 'latest':
                    // 按创建日期排序（假设较新的帖子有较高的ID）
                    usort($allPosts, function($a, $b) {
                        return $b['id'] - $a['id'];
                    });
                    break;
                case 'popular':
                    // 按浏览量排序
                    usort($allPosts, function($a, $b) {
                        return $b['views'] - $a['views'];
                    });
                    break;
                case 'domestic':
                    // 筛选出国内的帖子
                    $allPosts = array_filter($allPosts, function($post) {
                        return stripos($post['title'], 'China') !== false || 
                               stripos($post['title'], 'Yunnan') !== false || 
                               stripos($post['title'], 'Xinjiang') !== false || 
                               stripos($post['title'], 'Beijing') !== false ||
                               stripos($post['title'], 'Tibet') !== false ||
                               stripos($post['title'], 'Sanya') !== false;
                    });
                    $allPosts = array_values($allPosts);
                    break;
                case 'overseas':
                    // 筛选出国外的帖子
                    $allPosts = array_filter($allPosts, function($post) {
                        return stripos($post['title'], 'China') === false && 
                               stripos($post['title'], 'Yunnan') === false && 
                               stripos($post['title'], 'Xinjiang') === false && 
                               stripos($post['title'], 'Beijing') === false &&
                               stripos($post['title'], 'Tibet') === false &&
                               stripos($post['title'], 'Sanya') === false;
                    });
                    $allPosts = array_values($allPosts);
                    break;
                default:
                    // 推荐排序（默认按点赞数）
                    usort($allPosts, function($a, $b) {
                        return $b['likes'] - $a['likes'];
                    });
                    break;
            }
            
            $posts = $allPosts;
        }
        
        // 获取用户收藏
        $userFavorites = [];
        if (auth()->check()) {
            try {
            $userFavorites = auth()->user()->favorites()->pluck('post_id')->toArray();
            } catch (\Exception $e) {
                // 如果获取收藏失败，忽略错误
                $userFavorites = [];
            }
        } elseif (session()->has('mock_user')) {
            $userFavorites = session()->get('user_favorites', []);
        }
        
        // 标记收藏状态
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
    private function getPostsByCategory($category, $page = 1, $limit = 12)
    {
        // Check if we should use the database
        try {
            if (config('app.use_database', false) && \DB::connection()->getPdo()) {
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
                
                // Apply pagination
                $query->skip(($page - 1) * $limit)->take($limit);
            
            // Get posts and format for view
                return $query->get()->map(function($post) {
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
        } catch (\Exception $e) {
            // Exception handling will be done in the caller method
        }
        
        // Fallback to mock data
        $allPosts = $this->getDummyPosts();
        
        // Apply category filtering to mock data
        switch ($category) {
            case 'latest':
                // Sort by mock date (assuming newer posts have higher IDs)
                usort($allPosts, function($a, $b) {
                    return $b['id'] - $a['id'];
                });
                break;
            case 'popular':
                // Sort by views
                usort($allPosts, function($a, $b) {
                    return $b['views'] - $a['views'];
                });
                break;
            case 'domestic':
                // Filter to only show posts with China in the title
                $allPosts = array_filter($allPosts, function($post) {
                    return stripos($post['title'], 'China') !== false || 
                           stripos($post['title'], 'Yunnan') !== false || 
                           stripos($post['title'], 'Xinjiang') !== false || 
                           stripos($post['title'], 'Beijing') !== false ||
                           stripos($post['title'], 'Tibet') !== false ||
                           stripos($post['title'], 'Sanya') !== false;
                });
                $allPosts = array_values($allPosts);
                break;
            case 'overseas':
                // Filter to only show posts without China in the title
                $allPosts = array_filter($allPosts, function($post) {
                    return stripos($post['title'], 'China') === false && 
                           stripos($post['title'], 'Yunnan') === false && 
                           stripos($post['title'], 'Xinjiang') === false && 
                           stripos($post['title'], 'Beijing') === false &&
                           stripos($post['title'], 'Tibet') === false &&
                           stripos($post['title'], 'Sanya') === false;
                });
                $allPosts = array_values($allPosts);
                break;
            default:
                // Default sorting for recommended (by likes)
                usort($allPosts, function($a, $b) {
                    return $b['likes'] - $a['likes'];
                });
                break;
        }
        
        // Apply pagination to mock data
        $offset = ($page - 1) * $limit;
        $posts = array_slice($allPosts, $offset, $limit);
        
        return $posts;
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
        
        $posts = [];
        $useMockData = true;
        
        // 检查数据库连接和配置
        if (config('app.use_database', false)) {
            try {
                // 尝试连接数据库但不执行查询
                \DB::connection()->getPdo();
                $useMockData = false;
            } catch (\Exception $e) {
                // 连接失败，使用模拟数据
                $useMockData = true;
            }
        }
        
        if (!$useMockData) {
            try {
                // 数据库连接成功，尝试查询
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
                // 查询失败，使用模拟数据
                $useMockData = true;
            }
        }
        
        // 如果使用模拟数据
        if ($useMockData) {
            // 获取所有模拟帖子
            $allPosts = $this->getDummyPosts();
            
            // 筛选基于位置的帖子
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
        $useMockData = true;
        
        // 检查数据库连接和配置，只有确认可以连接时才尝试数据库查询
        if (config('app.use_database', false)) {
            try {
                // 尝试连接数据库但不执行查询
                \DB::connection()->getPdo();
                $useMockData = false;
            } catch (\Exception $e) {
                // 连接失败，使用模拟数据
                $useMockData = true;
            }
        }
        
        if (!$useMockData) {
            try {
                // 数据库连接成功，尝试查询
                $posts = Post::where(function($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('content', 'like', "%{$query}%")
                      ->orWhere('destination', 'like', "%{$query}%")
                      ->orWhere('duration', 'like', "%{$query}%");
                    
                    // Search in JSON tags field - 使用更安全的方式检查JSON标签
                    $q->orWhereRaw("JSON_CONTAINS(LOWER(tags), LOWER(?))", ['"' . strtolower($query) . '"']);
                })
                ->with('user')
                ->limit(8)
                ->get()
                ->map(function($post) {
                    // Format post data for view
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'cover_image' => $post->cover_image,
                        'duration' => $post->duration,
                        'cost' => $post->cost ?? null,
                        'destination' => $post->destination,
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
            } catch (\Exception $e) {
                // 查询失败，使用模拟数据
                $useMockData = true;
            }
        }
        
        // 如果数据库连接或查询失败，使用模拟数据
        if ($useMockData) {
            // Get dummy posts
        $allPosts = $this->getDummyPosts();
            
            // Filter posts based on search term
            $posts = array_filter($allPosts, function($post) use ($query) {
                $query = strtolower($query);
                
                // Check if query exists in title, destination, or content
                return strpos(strtolower($post['title']), $query) !== false ||
                    (isset($post['destination']) && strpos(strtolower($post['destination']), $query) !== false) ||
                    (isset($post['content']) && strpos(strtolower($post['content']), $query) !== false);
            });
            
            $posts = array_values($posts);
            
            // Limit to 8 results
            $posts = array_slice($posts, 0, 8);
        }
        
        // Get user favorites
        $userFavorites = [];
        if (auth()->check()) {
            try {
                $userFavorites = auth()->user()->favorites()->pluck('post_id')->toArray();
            } catch (\Exception $e) {
                // 如果获取收藏失败，忽略错误继续执行
                $userFavorites = [];
            }
        } elseif (session()->has('mock_user')) {
            $userFavorites = session()->get('user_favorites', []);
        }
        
        // Mark favorites
        foreach ($posts as &$post) {
            $post['is_favorite'] = in_array($post['id'], $userFavorites);
        }
        
        return response()->json(['posts' => $posts]);
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
        $useMockData = true;
        
        // 检查数据库连接和配置
        if (config('app.use_database', false)) {
            try {
                // 尝试连接数据库但不执行查询
                \DB::connection()->getPdo();
                $useMockData = false;
            } catch (\Exception $e) {
                // 连接失败，使用模拟数据
                $useMockData = true;
            }
        }
        
        if (!$useMockData) {
            try {
                // 数据库连接成功，获取帖子
                $posts = $this->getPostsFromDatabase($category, $page, $limit);
            } catch (\Exception $e) {
                // 查询失败，使用模拟数据
                $useMockData = true;
            }
        }
        
        // 如果使用模拟数据
        if ($useMockData) {
            // 获取所有模拟帖子
            $allPosts = $this->getDummyPosts();
            
            // 应用分类过滤
            switch ($category) {
                case 'latest':
                    // 按创建日期排序（假设较新的帖子有较高的ID）
                    usort($allPosts, function($a, $b) {
                        return $b['id'] - $a['id'];
                    });
                    break;
                case 'popular':
                    // 按浏览量排序
                    usort($allPosts, function($a, $b) {
                        return $b['views'] - $a['views'];
                    });
                    break;
                case 'domestic':
                    // 筛选出国内的帖子
                    $allPosts = array_filter($allPosts, function($post) {
                        return stripos($post['title'], 'China') !== false || 
                               stripos($post['title'], 'Yunnan') !== false || 
                               stripos($post['title'], 'Xinjiang') !== false || 
                               stripos($post['title'], 'Beijing') !== false ||
                               stripos($post['title'], 'Tibet') !== false ||
                               stripos($post['title'], 'Sanya') !== false;
                    });
                    $allPosts = array_values($allPosts);
                    break;
                case 'overseas':
                    // 筛选出国外的帖子
                    $allPosts = array_filter($allPosts, function($post) {
                        return stripos($post['title'], 'China') === false && 
                               stripos($post['title'], 'Yunnan') === false && 
                               stripos($post['title'], 'Xinjiang') === false && 
                               stripos($post['title'], 'Beijing') === false &&
                               stripos($post['title'], 'Tibet') === false &&
                               stripos($post['title'], 'Sanya') === false;
                    });
                    $allPosts = array_values($allPosts);
                    break;
                default:
                    // 推荐排序（默认按点赞数）
                    usort($allPosts, function($a, $b) {
                        return $b['likes'] - $a['likes'];
                    });
                    break;
            }
            
            // 应用分页
            $offset = ($page - 1) * $limit;
            $posts = array_slice($allPosts, $offset, $limit);
            
            // 如果没有更多帖子，返回空数组
            if (empty($posts)) {
                return response()->json(['posts' => []]);
            }
        }
        
        // 获取用户收藏
        $userFavorites = [];
        if (auth()->check()) {
            try {
                $userFavorites = auth()->user()->favorites()->pluck('post_id')->toArray();
            } catch (\Exception $e) {
                // 如果获取收藏失败，忽略错误
                $userFavorites = [];
            }
        } elseif (session()->has('mock_user')) {
            $userFavorites = session()->get('user_favorites', []);
        }
        
        // 标记收藏状态
        foreach ($posts as &$post) {
            $post['is_favorite'] = in_array($post['id'], $userFavorites);
        }
        
        return response()->json(['posts' => $posts]);
    }
    
    /**
     * 从数据库获取帖子（带分类和分页）
     */
    private function getPostsFromDatabase($category, $page = 1, $limit = 12)
    {
        // 创建查询构建器
        $query = Post::with('user');
        
        // 根据分类应用不同的查询条件
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
                // 推荐排序
                $query->orderBy('likes', 'desc');
                break;
        }
        
        // 应用分页
        $query->skip(($page - 1) * $limit)->take($limit);
        
        // 获取并格式化数据
        return $query->get()->map(function($post) {
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
} 