<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HotelController extends Controller
{
    /**
     * Display the hotel search page with filtering.
     */
    public function index(Request $request)
    {
        // Default city is now Hong Kong
        $city = $request->query('city', 'Hong Kong');

        // Default dates (today and tomorrow)
        $today = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');

        // Get query parameters including filters
        $checkin = $request->query('checkin', $today);
        $checkout = $request->query('checkout', $tomorrow);
        $guests = $request->query('guests', 1);
        $rooms = $request->query('rooms', 1);
        $hotelClass = $request->query('hotel_class');
        $keywords = $request->query('keywords');

        // Get weather data
        $weather = $this->getWeatherData($city);

        // Get ALL mock hotel data for the selected city first
        $allHotelsInCity = $this->getHotels($city);

        // Apply filters
        $filteredHotels = collect($allHotelsInCity) // Use Laravel Collection for easier filtering
            ->when($hotelClass, function ($collection, $hotelClass) {
                // Filter by stars if hotel_class is provided
                return $collection->where('stars', $hotelClass);
            })
            ->when($keywords, function ($collection, $keywords) {
                // Filter by keywords (case-insensitive search in name and tags)
                $keywords = strtolower($keywords);
                return $collection->filter(function ($hotel) use ($keywords) {
                    $inName = stripos(strtolower($hotel['name']), $keywords) !== false;
                    $inTags = false;
                    if (isset($hotel['tags']) && is_array($hotel['tags'])) {
                        foreach ($hotel['tags'] as $tag) {
                            if (stripos(strtolower($tag), $keywords) !== false) {
                                $inTags = true;
                                break;
                            }
                        }
                    }
                    return $inName || $inTags;
                });
            })
            ->values() // Reset keys after filtering
            ->all();

        // Get popular cities data
        $popularCities = $this->getPopularCities();

        // Note: Pagination would typically be applied here if using DB queries.
        // For mock data, we're passing the filtered array directly.

        return view('hotels.index', [
            'city' => $city,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'guests' => $guests,
            'rooms' => $rooms,
            'weather' => $weather,
            'hotels' => $filteredHotels,
            'popularCities' => $popularCities
        ]);
    }

    /**
     * Search redirect (keeps URL clean with GET parameters)
     */
    public function search(Request $request)
    {
        $city = $request->input('city');
        $checkin = $request->input('checkin');
        $checkout = $request->input('checkout');
        $guests = $request->input('guests', 1);
        $rooms = $request->input('rooms', 1);

        // If no city is provided, redirect to the homepage
        if (empty($city)) {
            return redirect()->route('hotels.index');
        }

        // Redirect to the index page with query parameters
        return redirect()->route('hotels.index', [
            'city' => $city,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'guests' => $guests,
            'rooms' => $rooms,
            'hotel_class' => $request->input('hotel_class'),
            'keywords' => $request->input('keywords'),
        ]);
    }

    /**
     * Get weather data from WeatherAPI
     */
    private function getWeatherData($city)
    {
        $apiKey = config('services.weatherapi.key');

        if (!$apiKey) {
            Log::error('WeatherAPI key not configured.');
            return [
                'temp' => 'N/A',
                'condition' => 'Config Error',
                'humidity' => 'N/A',
                'wind' => 'N/A',
                'icon' => null,
            ];
        }

        $apiUrl = "http://api.weatherapi.com/v1/current.json?key={$apiKey}&q=" . urlencode($city) . "&aqi=no";

        try {
            $response = Http::timeout(5)->get($apiUrl);
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['current'])) {
                    $iconUrl = $data['current']['condition']['icon'];
                    if (strpos($iconUrl, '//') === 0) {
                        $iconUrl = 'https:' . $iconUrl;
                    }
                    return [
                        'temp'      => $data['current']['temp_c'],
                        'condition' => $data['current']['condition']['text'],
                        'humidity'  => $data['current']['humidity'] . '%',
                        'wind'      => $data['current']['wind_kph'] . ' km/h',
                        'icon'      => $iconUrl
                    ];
                } else {
                    Log::warning('WeatherAPI response missing current data for city: ' . $city, ['response' => $data]);
                    return null;
                }
            } else {
                Log::error('WeatherAPI request failed for city: ' . $city, [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('WeatherAPI connection error for city: ' . $city . ' - ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching weather data for city: ' . $city . ' - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get mock hotel data.
     */
    private function getHotels($city)
    {
        // Use unique IDs across all cities
        $allHotels = [
            'Hong Kong' => [
                ['id' => 1, 'name' => 'The Peninsula Hong Kong', 'rating' => 4.9, 'stars' => 5, 'distance' => '1 km from Tsim Sha Tsui', 'price' => 3500, 'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Luxury', 'Harbour View', 'Spa']],
                ['id' => 2, 'name' => 'Four Seasons Hotel Hong Kong', 'rating' => 4.8, 'stars' => 5, 'distance' => '0.5 km from IFC', 'price' => 3200, 'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Michelin Stars', 'Pool', 'Central']],
                ['id' => 3, 'name' => 'Cordis, Hong Kong', 'rating' => 4.5, 'stars' => 5, 'distance' => '0.2 km from Langham Place', 'price' => 1800, 'image' => 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Shopping', 'Mong Kok', 'Rooftop Pool']],
                ['id' => 4, 'name' => 'Hotel ICON', 'rating' => 4.6, 'stars' => 4, 'distance' => '0.8 km from Tsim Sha Tsui East', 'price' => 1500, 'image' => 'https://images.unsplash.com/photo-1568084680786-a84f91d1153c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Design', 'University', 'Pool View']],
                ['id' => 5, 'name' => 'ibis Hong Kong Central & Sheung Wan', 'rating' => 4.0, 'stars' => 3, 'distance' => '0.5 km from Sheung Wan MTR', 'price' => 700, 'image' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Budget', 'Value', 'Harbour View (Partial)']],
            ],
            'Beijing' => [
                ['id' => 101, 'name' => 'Grand International Hotel Beijing', 'rating' => 4.5, 'stars' => 5, 'distance' => '2.5 km from Tiananmen Square', 'price' => 880, 'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Free Breakfast', 'Free WiFi', 'Fitness Center']],
                ['id' => 102, 'name' => 'The Ritz-Carlton, Beijing', 'rating' => 4.8, 'stars' => 5, 'distance' => '3 km from The Palace Museum', 'price' => 1680, 'image' => 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Fine Dining', 'Swimming Pool', 'Spa Center']],
                ['id' => 103, 'name' => 'Park Hyatt Beijing', 'rating' => 4.7, 'stars' => 5, 'distance' => '1 km from CCTV Headquarters', 'price' => 1500, 'image' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['CBD', 'Modern', 'Sky Lobby']],
                ['id' => 104, 'name' => 'Hotel Éclat Beijing', 'rating' => 4.6, 'stars' => 4, 'distance' => '2 km from Sanlitun', 'price' => 1200, 'image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Art', 'Boutique', 'Unique Design']],
            ],
            'Shanghai' => [
                ['id' => 201, 'name' => 'Waldorf Astoria Shanghai on the Bund', 'rating' => 4.9, 'stars' => 5, 'distance' => '0.1 km from The Bund', 'price' => 2800, 'image' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Heritage', 'Luxury', 'Bund View']],
                ['id' => 202, 'name' => 'The PuLi Hotel and Spa', 'rating' => 4.8, 'stars' => 5, 'distance' => '0.5 km from Jing\'an Temple', 'price' => 2500, 'image' => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Urban Resort', 'Spa', 'Infinity Pool']],
                ['id' => 203, 'name' => 'Cachet Boutique Shanghai', 'rating' => 4.5, 'stars' => 4, 'distance' => '1 km from Nanjing Road', 'price' => 1300, 'image' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Boutique', 'Stylish', 'Central Location']],
                ['id' => 204, 'name' => 'The Phoenix Hostel Shanghai', 'rating' => 4.3, 'stars' => 2, 'distance' => '1.2 km from People\'s Square', 'price' => 250, 'image' => 'https://images.unsplash.com/photo-1564501049412-61c2a3083791?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Hostel', 'Social', 'Affordable']],
                ['id' => 205, 'name' => 'Jinjiang Inn Shanghai Nanjing Road Pedestrian Street', 'rating' => 4.0, 'stars' => 3, 'distance' => '0.3 km from Nanjing Road', 'price' => 550, 'image' => 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Value', 'Pedestrian Street', 'Convenient']],
            ],
            'Nanjing' => [
                ['id' => 301, 'name' => 'The Ritz-Carlton, Nanjing', 'rating' => 4.8, 'stars' => 5, 'distance' => '0.5 km from Deji Plaza', 'price' => 1700, 'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Luxury', 'Xinjiekou', 'City View']],
                ['id' => 302, 'name' => 'Fairmont Nanjing', 'rating' => 4.6, 'stars' => 5, 'distance' => '1 km from Olympic Sports Center', 'price' => 1100, 'image' => 'https://images.unsplash.com/photo-1444201983204-c43cbd584d93?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Modern', 'Business', 'River View']],
                ['id' => 303, 'name' => 'Novotel Nanjing Central Suning', 'rating' => 4.4, 'stars' => 4, 'distance' => '0.8 km from Xinjiekou', 'price' => 800, 'image' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Central', 'Comfortable', 'Metro Access']],
                ['id' => 304, 'name' => 'Nanjing Youth Hostel', 'rating' => 4.1, 'stars' => 2, 'distance' => '1.5 km from Confucius Temple', 'price' => 150, 'image' => 'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Hostel', 'Budget', 'Fuzimiao Area']],
                ['id' => 305, 'name' => 'Holiday Inn Nanjing Aqua City', 'rating' => 4.3, 'stars' => 3, 'distance' => '0.2 km from Aqua City Mall', 'price' => 600, 'image' => 'https://images.unsplash.com/photo-1590073242678-70ee3fc28e8e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', 'tags' => ['Shopping', 'Family Friendly', 'Convenient']],
            ],
        ];
        return $allHotels[$city] ?? []; // Return empty if city not found
    }

    /**
     * Hotel details page.
     */
    public function show($id)
    {
        $hotel = $this->getHotelById($id);
        if (!$hotel) {
            // Error message is already in English
            return redirect()->route('hotels.index')->with('error', 'Hotel not found.');
        }
        // Assuming 'hotels.show' view exists
        return view('hotels.show', [
            'hotel' => $hotel
        ]);
    }

    /**
     * Subscribe to hotel price changes.
     */
    public function subscribe(Request $request, $id)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'price_threshold' => 'nullable|numeric'
        ]);
        // TODO: Implement actual subscription saving logic here

        // Mock success response is already in English
        return response()->json([
            'success' => true,
            'message' => 'Subscription successful! We will notify you of price changes.'
        ]);
    }

    /**
     * Get hotel details by ID from the mock data.
     */
    private function getHotelById($id)
    {
        // Ensure all cities with mock data are included here
        $allCityKeys = ['Hong Kong', 'Beijing', 'Shanghai', 'Nanjing'];
        $allHotels = [];
        foreach ($allCityKeys as $cityKey) {
            $allHotels = array_merge($allHotels, $this->getHotels($cityKey));
        }

        foreach ($allHotels as $hotel) {
            if ($hotel['id'] == $id) {
                return $hotel;
            }
        }
        return null;
    }

    /**
     * Get popular cities data.
     */
    private function getPopularCities()
    {
        $domestic = [
            'Beijing',
            'Shanghai',
            'Tianjin',
            'Chongqing',
            'Dalian',
            'Qingdao',
            'Xi an',
            'Nanjing',
            'Suzhou',
            'Hangzhou',
            'Xiamen',
            'Chengdu',
            'Shenzhen',
            'Guangzhou',
            'Sanya',
            'Taipei',
            'Hong Kong',
            'Jinan',
            'Ningbo',
            'Shenyang',
            'Wuhan',
            'Zhengzhou'
        ];
        $domestic = array_unique($domestic);

        return [
            'domestic' => array_values($domestic),
            'international' => [
                'Seoul',
                'Bangkok',
                'Phuket',
                'Tokyo',
                'Singapore',
                'Osaka',
                'Jeju',
                'Bali',
                'Chiang Mai',
                'Kota Kinabalu',
                'Kyoto',
                'Kuala Lumpur',
                'Pattaya',
                'Okinawa',
                'Los Angeles',
                'Koh Samui',
                'Paris',
                'Krabi',
                'Las Vegas',
                'London',
                'New York',
                'Nha Trang',
                'Sydney'
            ]
        ];
    }
}
