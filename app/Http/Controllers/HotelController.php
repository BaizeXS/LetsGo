<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HotelController extends Controller
{
    /**
     * Display the hotel search page
     */
    public function index(Request $request)
    {
        // Get current city (default to Beijing for example, change if needed)
        $city = $request->query('city', 'Beijing'); // Keep city name itself, or translate if needed

        // Default dates (today and tomorrow)
        $today = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');

        // Get query parameters
        $checkin = $request->query('checkin', $today);
        $checkout = $request->query('checkout', $tomorrow);
        $guests = $request->query('guests', 1);
        $rooms = $request->query('rooms', 1);

        // Get weather data
        $weather = $this->getWeatherData($city); // Assumes city name is used for API

        // Mock hotel data (Keep names as they are, or translate if needed)
        $hotels = $this->getHotels($city);

        // Get popular cities data
        $popularCities = $this->getPopularCities();

        return view('hotels.index', [
            'city' => $city,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'guests' => $guests,
            'rooms' => $rooms,
            'weather' => $weather,
            'hotels' => $hotels,
            'popularCities' => $popularCities
        ]);
    }

    /**
     * Search for hotels
     */
    public function search(Request $request)
    {
        // Get search parameters
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
            'rooms' => $rooms
        ]);
    }

    /**
     * Get weather data
     * In a real application, this would use a Weather API call
     */
    private function getWeatherData($city)
    {
        // Mock weather data and different conditions
        $weatherConditions = ['Sunny', 'Cloudy', 'Rainy', 'Partly Cloudy', 'Humid'];
        // Use city name as seed to ensure the same city always gets the same weather (for demo)
        $cityHash = crc32($city);
        srand($cityHash);
        $conditionIndex = rand(0, count($weatherConditions) - 1);
        $condition = $weatherConditions[$conditionIndex];
        $temperature = rand(15, 35);
        $humidity = rand(30, 90);
        $windSpeed = rand(0, 20);
        srand(); // Reset seed

        return [
            'temp' => $temperature . '°C',
            'condition' => $condition,
            'humidity' => $humidity . '%',
            'wind' => $windSpeed . ' km/h'
        ];
    }

    /**
     * Get mock hotel data
     */
    private function getHotels($city)
    {
        // Example: Use English names if necessary, or keep original names
        $hotels = [
            'Beijing' => [
                [
                    'id' => 101,
                    'name' => 'Grand International Hotel Beijing',
                    'rating' => 4.5,
                    'stars' => 5,
                    'distance' => '2.5 km from Tiananmen Square',
                    'price' => 880, // Use currency symbol later if needed
                    'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'tags' => ['Free Breakfast', 'Free WiFi', 'Fitness Center']
                ],
                [
                    'id' => 102,
                    'name' => 'The Ritz-Carlton, Beijing',
                    'rating' => 4.8,
                    'stars' => 5,
                    'distance' => '3 km from The Palace Museum',
                    'price' => 1680,
                    'image' => 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'tags' => ['Fine Dining', 'Swimming Pool', 'Spa Center']
                ]
            ],
            'Shanghai' => [ // Example for Shanghai
                [
                    'id' => 201,
                    'name' => 'InterContinental Grand Stanford Hong Kong', // Kept original name as example
                    'rating' => 4.4,
                    'stars' => 5,
                    'distance' => '5.9 km from Hong Kong Coliseum',
                    'price' => 1902,
                    'image' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'tags' => ['Instant Confirmation', 'Free Shuttle', 'Vintage Style']
                ],
                [
                    'id' => 202,
                    'name' => 'Regal Hongkong Hotel', // Kept original name as example
                    'rating' => 4.0,
                    'stars' => 5,
                    'distance' => '5.3 km from North Point',
                    'price' => 680,
                    'image' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'tags' => ['Instant Confirmation', 'Free Luggage Storage', 'Airport Shuttle']
                ]
            ]
            // Add other cities as needed
        ];
        return $hotels[$city] ?? []; // Return empty if city not found
    }

    /**
     * Hotel details page
     */
    public function show($id)
    {
        $hotel = $this->getHotelById($id);
        if (!$hotel) {
            // Translate error message
            return redirect()->route('hotels.index')->with('error', 'Hotel not found.');
        }
        return view('hotels.show', [
            'hotel' => $hotel
        ]);
    }

    /**
     * Subscribe to hotel price changes
     */
    public function subscribe(Request $request, $id)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'price_threshold' => 'nullable|numeric'
        ]);
        // In a real app, save subscription info to DB
        // Mock success response (translated)
        return response()->json([
            'success' => true,
            'message' => 'Subscription successful! We will notify you of price changes.'
        ]);
    }

    /**
     * Get hotel details by ID
     */
    private function getHotelById($id)
    {
        // Combine all hotel sources for lookup
        $allHotels = array_merge(
            $this->getHotels('Beijing'),
            $this->getHotels('Shanghai')
            // Add other cities if needed
        );
        foreach ($allHotels as $hotel) {
            if ($hotel['id'] == $id) {
                return $hotel;
            }
        }
        return null;
    }

    /**
     * Get popular cities data (Translated to English)
     */
    private function getPopularCities()
    {
        return [
            'domestic' => [ // Assuming 'domestic' means within the app's primary region, e.g., China
                'Beijing',
                'Shanghai',
                'Tianjin',
                'Chongqing',
                'Dalian',
                'Qingdao',
                'Xi\'an',
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
            ],
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
