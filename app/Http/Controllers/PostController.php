<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Show single post details
     */
    public function show($id)
    {
        // In a real application, post information should be retrieved from the database
        $post = [
            'id' => $id,
            'title' => 'Hokkaido 7-Day Trip Guide',
            'content' => 'This is a detailed guide for a 7-day trip to Hokkaido...',
            'cover_image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
            'images' => [
                'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1494783367193-149034c05e8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
            ],
            'duration' => '7 Days 6 Nights',
            'date' => 'Departing December 24',
            'cost' => '12k per person',
            'user' => [
                'id' => 1,
                'name' => 'Travel Expert',
                'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg'
            ],
            'views' => 1245,
            'likes' => 324,
            'comments' => [
                [
                    'id' => 1,
                    'user' => [
                        'name' => 'Mike',
                        'avatar' => 'https://randomuser.me/api/portraits/men/32.jpg'
                    ],
                    'content' => 'Great! I want to go too!',
                    'created_at' => '2 hours ago'
                ],
                [
                    'id' => 2,
                    'user' => [
                        'name' => 'Travel Enthusiast',
                        'avatar' => 'https://randomuser.me/api/portraits/women/28.jpg'
                    ],
                    'content' => 'What kind of clothes should I prepare for Hokkaido in winter?',
                    'created_at' => '3 hours ago'
                ]
            ],
            'created_at' => '2023-12-15'
        ];
        
        return view('posts.show', ['post' => $post]);
    }
    
    /**
     * Show create post form
     */
    public function create()
    {
        return view('posts.create');
    }
    
    /**
     * Store a newly created post
     */
    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'cover_image' => 'required|image|max:2048',
            'images.*' => 'image|max:2048',
            'duration' => 'required|max:50',
            'cost' => 'nullable|max:50',
        ]);
        
        // Process image upload
        // $coverPath = $request->file('cover_image')->store('posts');
        
        // Save post information to database
        // Post::create([...]);
        
        return redirect()->route('home')->with('success', 'Post published successfully!');
    }
    
    /**
     * Toggle favorite status
     */
    public function toggleFavorite($id)
    {
        // In a real application, this should save/delete user favorite records
        return response()->json(['success' => true]);
    }
    
    /**
     * Toggle like status
     */
    public function toggleLike($id)
    {
        // In a real application, this should save/delete user like records
        return response()->json(['success' => true]);
    }
    
    /**
     * Generate a travel route map
     */
    public function generateRouteMap(Request $request)
    {
        // 验证请求
        $validated = $request->validate([
            'postId' => 'required|integer',
            'content' => 'required|string',
        ]);
        
        try {
            // 实际项目中，这里应该调用AI处理服务或第三方API
            // 生成路线图，这里我们模拟返回一个预设的图片
            
            // 根据内容提取行程信息
            $itinerary = $this->extractItinerary($request->content);
            
            // 模拟生成路线图的延迟
            sleep(1);
            
            // 使用腾讯地图静态图API
            $tencentMapKey = env('TENCENT_MAP_KEY', '');
            
            // 不同路线的样本
            $sampleRoutes = [
                // 日本行程
                [
                    'center' => '35.68925,139.69234', // 东京
                    'markers' => 'markers=color:blue|label:A|35.68925,139.69234&markers=color:blue|label:B|34.6937,135.5022&markers=color:blue|label:C|35.0116,135.7681',
                    'path' => 'color:0x0000ff50|weight:5|35.68925,139.69234;34.6937,135.5022;35.0116,135.7681',
                    'zoom' => '6',
                    'size' => '600*400'
                ],
                // 中国行程
                [
                    'center' => '39.9042,116.4074', // 北京
                    'markers' => 'markers=color:blue|label:A|39.9042,116.4074&markers=color:blue|label:B|34.342,108.939&markers=color:blue|label:C|31.2304,121.4737',
                    'path' => 'color:0x0000ff50|weight:5|39.9042,116.4074;34.342,108.939;31.2304,121.4737',
                    'zoom' => '5',
                    'size' => '600*400'
                ],
                // 法国行程
                [
                    'center' => '48.8566,2.3522', // 巴黎
                    'markers' => 'markers=color:blue|label:A|48.8566,2.3522&markers=color:blue|label:B|45.764,4.8357&markers=color:blue|label:C|43.7102,7.2620',
                    'path' => 'color:0x0000ff50|weight:5|48.8566,2.3522;45.764,4.8357;43.7102,7.2620',
                    'zoom' => '6',
                    'size' => '600*400'
                ]
            ];
            
            // 随机选择一条路线
            $selectedRoute = $sampleRoutes[array_rand($sampleRoutes)];
            
            // 构造腾讯地图静态图URL
            $mapUrl = sprintf(
                'https://apis.map.qq.com/ws/staticmap/v2?key=%s&center=%s&%s&paths=%s&zoom=%s&size=%s',
                $tencentMapKey,
                $selectedRoute['center'],
                $selectedRoute['markers'],
                $selectedRoute['path'],
                $selectedRoute['zoom'],
                $selectedRoute['size']
            );
            
            return response()->json([
                'success' => true,
                'mapUrl' => $mapUrl,
                'itinerary' => $itinerary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate route map: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Extract itinerary information from post content
     */
    private function extractItinerary($content)
    {
        // 实际项目中，这里应该用NLP或其他AI技术分析文本
        // 提取出行程信息，这里我们简单模拟
        
        // 假设我们能识别出这些模式：
        $dayRegex = '/Day\s*(\d+)[^\n]*/i';
        $locationRegex = '/(?:visited|went to|arrived at|stopped by|explored)\s+([^\.,:;\n]+)/i';
        
        $itinerary = [];
        
        // 提取天数
        preg_match_all($dayRegex, $content, $dayMatches);
        if (!empty($dayMatches[0])) {
            foreach ($dayMatches[0] as $index => $dayMatch) {
                $day = $dayMatches[1][$index];
                $itinerary["Day {$day}"] = [];
                
                // 查找这个天数后面提到的地点
                preg_match_all($locationRegex, $content, $locationMatches);
                if (!empty($locationMatches[1])) {
                    foreach ($locationMatches[1] as $location) {
                        $itinerary["Day {$day}"][] = trim($location);
                    }
                }
            }
        }
        
        // 如果没有提取到结构化信息，返回一些假数据
        if (empty($itinerary)) {
            $itinerary = [
                'Day 1' => ['Airport', 'Hotel', 'Local Restaurant'],
                'Day 2' => ['Popular Attraction 1', 'Lunch', 'Popular Attraction 2'],
                'Day 3' => ['Day Trip', 'Souvenir Shopping', 'Farewell Dinner']
            ];
        }
        
        return $itinerary;
    }
} 