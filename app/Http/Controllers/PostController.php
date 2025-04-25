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
        // Add debug log
        \Log::info('Toggle favorite request received', [
            'id' => $id,
            'user' => auth()->user() ? auth()->user()->id : 'guest',
            'session_has_mock_user' => session()->has('mock_user'),
            'is_ajax' => request()->ajax(),
            'wants_json' => request()->wantsJson(),
            'content_type' => request()->header('Content-Type')
        ]);
        
        // Simplify response structure
        $isFavorite = false;
        $success = true;
        $message = '';
        
        // Get the current user
        $user = auth()->user();
        
        // For development with session-based mock login
        if (!$user && session()->has('mock_user')) {
            // Get current favorites from session
            $favorites = session()->get('user_favorites', []);
            
            // Toggle favorite status
            if (in_array($id, $favorites)) {
                $favorites = array_diff($favorites, [$id]);
                $message = 'Post removed from favorites';
                $isFavorite = false;
            } else {
                $favorites[] = $id;
                $message = 'Post added to favorites';
                $isFavorite = true;
            }
            
            // Store updated favorites in session
            session()->put('user_favorites', $favorites);
        } 
        // For database-based auth when implemented
        else if ($user) {
            // When using database, toggle the favorite relationship
            if ($user->favorites()->where('post_id', $id)->exists()) {
                $user->favorites()->detach($id);
                $message = 'Post removed from favorites';
                $isFavorite = false;
            } else {
                $user->favorites()->attach($id);
                $message = 'Post added to favorites';
                $isFavorite = true;
            }
        } else {
            // User not logged in
            return response()->json([
                'success' => false,
                'message' => 'Please login to favorite posts'
            ], 401);
        }

        // Return JSON response
        return response()->json([
            'success' => $success,
            'message' => $message,
            'isFavorite' => $isFavorite
        ]);
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
        // Validate request
        $validated = $request->validate([
            'postId' => 'required|integer',
            'content' => 'required|string',
        ]);
        
        try {
            // In an actual project, this should call an AI processing service or third-party API
            // to generate a route map. Here we simulate returning a preset image
            
            // Extract itinerary information from content
            $itinerary = $this->extractItinerary($request->content);
            
            // Simulate delay for route map generation
            sleep(1);
            
            // Use Tencent Maps Static API
            $tencentMapKey = env('TENCENT_MAP_KEY', '');
            
            // Sample routes for different destinations
            $sampleRoutes = [
                // Japan itinerary
                [
                    'center' => '35.68925,139.69234', // Tokyo
                    'markers' => 'markers=color:blue|label:A|35.68925,139.69234&markers=color:blue|label:B|34.6937,135.5022&markers=color:blue|label:C|35.0116,135.7681',
                    'path' => 'color:0x0000ff50|weight:5|35.68925,139.69234;34.6937,135.5022;35.0116,135.7681',
                    'zoom' => '6',
                    'size' => '600*400'
                ],
                // China itinerary
                [
                    'center' => '39.9042,116.4074', // Beijing
                    'markers' => 'markers=color:blue|label:A|39.9042,116.4074&markers=color:blue|label:B|34.342,108.939&markers=color:blue|label:C|31.2304,121.4737',
                    'path' => 'color:0x0000ff50|weight:5|39.9042,116.4074;34.342,108.939;31.2304,121.4737',
                    'zoom' => '5',
                    'size' => '600*400'
                ],
                // France itinerary
                [
                    'center' => '48.8566,2.3522', // Paris
                    'markers' => 'markers=color:blue|label:A|48.8566,2.3522&markers=color:blue|label:B|45.764,4.8357&markers=color:blue|label:C|43.7102,7.2620',
                    'path' => 'color:0x0000ff50|weight:5|48.8566,2.3522;45.764,4.8357;43.7102,7.2620',
                    'zoom' => '6',
                    'size' => '600*400'
                ]
            ];
            
            // Randomly select a route
            $selectedRoute = $sampleRoutes[array_rand($sampleRoutes)];
            
            // Construct Tencent Maps Static API URL
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
        // In an actual project, this should use NLP or other AI technology to analyze the text
        // and extract itinerary information. Here we simply simulate this process
        
        // Assume we can identify these patterns:
        $dayRegex = '/Day\s*(\d+)[^\n]*/i';
        $locationRegex = '/(?:visited|went to|arrived at|stopped by|explored)\s+([^\.,:;\n]+)/i';
        
        $itinerary = [];
        
        // Extract days
        preg_match_all($dayRegex, $content, $dayMatches);
        if (!empty($dayMatches[0])) {
            foreach ($dayMatches[0] as $index => $dayMatch) {
                $day = $dayMatches[1][$index];
                $itinerary["Day {$day}"] = [];
                
                // Find locations mentioned after this day
                preg_match_all($locationRegex, $content, $locationMatches);
                if (!empty($locationMatches[1])) {
                    foreach ($locationMatches[1] as $location) {
                        $itinerary["Day {$day}"][] = trim($location);
                    }
                }
            }
        }
        
        // If no structured information was extracted, return some dummy data
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