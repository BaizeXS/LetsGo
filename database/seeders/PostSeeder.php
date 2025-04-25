<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure we have users
        if (User::count() === 0) {
            User::factory()->count(5)->create();
        }
        
        $users = User::all();
        
        // Sample posts data
        $posts = [
            [
                'title' => 'Hokkaido 7-Day Trip',
                'content' => 'Experience the beauty of Hokkaido in this 7-day adventure. From the lavender fields of Furano to the stunning blue pond of Biei, this trip covers the most scenic spots of Japan\'s northern island. Enjoy fresh seafood in Otaru and relax in the natural hot springs of Noboribetsu.',
                'destination' => 'Hokkaido, Japan',
                'cover_image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                    'https://images.unsplash.com/photo-1494783367193-149034c05e8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                ]),
                'duration' => '7 Days 6 Nights',
                'cost' => '12k per person',
                'date' => '2023-12-24',
                'tags' => json_encode(['Japan', 'Winter', 'Hot Springs', 'Food', 'Nature']),
            ],
            [
                'title' => 'Xinjiang Duku Highway Adventure',
                'content' => 'Embark on a thrilling journey along the Duku Highway, one of China\'s most beautiful roads. This route takes you through snow-capped mountains, vast grasslands, and pristine lakes of Xinjiang. Experience local Uyghur culture and cuisine along the way.',
                'destination' => 'Xinjiang, China',
                'cover_image' => 'https://images.unsplash.com/photo-1494783367193-149034c05e8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1494783367193-149034c05e8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                ]),
                'duration' => '5 Days',
                'cost' => '4k per person',
                'date' => '2023-06-15',
                'tags' => json_encode(['China', 'Road Trip', 'Mountains', 'Culture', 'Adventure']),
            ],
            [
                'title' => 'Paris Museum Tour',
                'content' => 'Discover the artistic treasures of Paris in this cultural journey through the city\'s most renowned museums. Visit the Louvre, Musée d\'Orsay, Centre Pompidou, and more. This itinerary includes skip-the-line tickets and expert guides for a deep dive into art history.',
                'destination' => 'Paris, France',
                'cover_image' => 'https://images.unsplash.com/photo-1431274172761-fca41d930114?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1431274172761-fca41d930114?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                ]),
                'duration' => '10 Days',
                'cost' => '8k per person',
                'date' => '2023-09-10',
                'tags' => json_encode(['France', 'Art', 'Museums', 'Culture', 'Europe']),
            ],
            [
                'title' => 'Yunnan Dali Erhai Lake Leisure Trip',
                'content' => 'Escape the hustle and bustle of city life with this relaxing journey to Dali, Yunnan. Explore the ancient town, cycle around Erhai Lake, and enjoy the slow pace of life. This trip focuses on immersing in the local Bai minority culture and enjoying the scenic beauty of this region.',
                'destination' => 'Dali, Yunnan, China',
                'cover_image' => 'https://images.unsplash.com/photo-1555217851-6141ab127fa8?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1555217851-6141ab127fa8?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                ]),
                'duration' => '4 Days 3 Nights',
                'cost' => '2.5k per person',
                'date' => '2023-04-05',
                'tags' => json_encode(['China', 'Yunnan', 'Relaxation', 'Culture', 'Lake']),
            ],
            [
                'title' => 'Tokyo Food Exploration',
                'content' => 'Embark on a culinary adventure through Tokyo\'s diverse food scene. From Michelin-starred restaurants to hidden street food stalls, this journey takes you through the best flavors of Japan. Highlights include a sushi-making class, a visit to Tsukiji Outer Market, and a food tour in Shinjuku.',
                'destination' => 'Tokyo, Japan',
                'cover_image' => 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                ]),
                'duration' => '6 Days',
                'cost' => '5k per person',
                'date' => '2023-11-20',
                'tags' => json_encode(['Japan', 'Food', 'Culinary', 'Urban', 'Asia']),
            ],
            [
                'title' => 'Chiang Mai Hidden Gems',
                'content' => 'Discover the lesser-known attractions of Chiang Mai in this unique itinerary. Beyond the usual temples and night markets, explore hidden cafes, artisan workshops, and local communities. This trip includes a day with elephants at an ethical sanctuary and a cooking class in a local farm.',
                'destination' => 'Chiang Mai, Thailand',
                'cover_image' => 'https://images.unsplash.com/photo-1528181304800-259b08848526?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1528181304800-259b08848526?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                ]),
                'duration' => '5 Days',
                'cost' => '3k per person',
                'date' => '2023-08-15',
                'tags' => json_encode(['Thailand', 'Off the beaten path', 'Culture', 'Food', 'Southeast Asia']),
            ],
            [
                'title' => 'Sanya Family Vacation Guide',
                'content' => 'Plan the perfect family getaway to Sanya, China\'s tropical paradise. This guide covers family-friendly resorts, beaches safe for children, and activities that both kids and parents will enjoy. Special tips for traveling with children of different age groups are included.',
                'destination' => 'Sanya, Hainan, China',
                'cover_image' => 'https://images.unsplash.com/photo-1540202404-a2f29016b523?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1540202404-a2f29016b523?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                ]),
                'duration' => '6 Days 5 Nights',
                'cost' => '6k for family',
                'date' => '2023-07-10',
                'tags' => json_encode(['China', 'Beach', 'Family', 'Resort', 'Tropical']),
            ],
            [
                'title' => 'Tibet Lhasa Pilgrimage',
                'content' => 'Embark on a spiritual journey to Tibet, the roof of the world. Visit sacred monasteries, witness devoted pilgrims, and learn about Tibetan Buddhism. This trip includes visits to Potala Palace, Jokhang Temple, and Sera Monastery, with opportunities to interact with local monks.',
                'destination' => 'Lhasa, Tibet, China',
                'cover_image' => 'https://images.unsplash.com/photo-1461823385004-d7660947a7c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1461823385004-d7660947a7c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                ]),
                'duration' => '8 Days',
                'cost' => '7k per person',
                'date' => '2023-05-25',
                'tags' => json_encode(['Tibet', 'Spiritual', 'Buddhism', 'Mountains', 'Culture']),
            ],
        ];
        
        // Create posts and associate with random users
        foreach ($posts as $postData) {
            $user = $users->random();
            $post = new Post($postData);
            $post->user_id = $user->id;
            $post->views = rand(500, 5000);
            $post->likes = rand(50, 1000);
            $post->save();
        }
    }
} 