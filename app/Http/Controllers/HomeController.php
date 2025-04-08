<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * 显示首页
     */
    public function index(Request $request)
    {
        // 获取当前分类
        $activeCategory = $request->query('category', 'recommended');
        
        // 模拟分类数据
        $categories = [
            ['name' => '推荐', 'slug' => 'recommended'],
            ['name' => '最新', 'slug' => 'latest'],
            ['name' => '热门', 'slug' => 'popular'],
            ['name' => '国内游', 'slug' => 'domestic'],
            ['name' => '海外游', 'slug' => 'overseas'],
            ['name' => '周边游', 'slug' => 'nearby'],
            ['name' => '特色游', 'slug' => 'special'],
            ['name' => '自由行', 'slug' => 'independent'],
            ['name' => '民宿', 'slug' => 'homestay'],
        ];
        
        // 模拟帖子数据
        $posts = $this->getDummyPosts();
        
        return view('home.index', [
            'activeCategory' => $activeCategory,
            'categories' => $categories,
            'posts' => $posts
        ]);
    }
    
    /**
     * 获取模拟帖子数据
     */
    private function getDummyPosts()
    {
        return [
            [
                'id' => 1,
                'title' => '北海道7日游',
                'cover_image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '7天6晚·12.24',
                'user' => [
                    'name' => '旅行达人',
                    'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg'
                ],
                'views' => 1245,
                'likes' => 324,
                'comments' => 56
            ],
            [
                'id' => 2,
                'title' => '新疆独库公路',
                'cover_image' => 'https://images.unsplash.com/photo-1494783367193-149034c05e8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '5天·人均4k',
                'cost' => '人均4k',
                'user' => [
                    'name' => '风之子',
                    'avatar' => 'https://randomuser.me/api/portraits/men/32.jpg'
                ],
                'views' => 2341,
                'likes' => 521,
                'comments' => 89
            ],
            [
                'id' => 3,
                'title' => '巴黎博物馆之旅',
                'cover_image' => 'https://images.unsplash.com/photo-1431274172761-fca41d930114?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '10天·艺术之旅',
                'user' => [
                    'name' => '艺术爱好者',
                    'avatar' => 'https://randomuser.me/api/portraits/women/28.jpg'
                ],
                'views' => 3450,
                'likes' => 728,
                'comments' => 134
            ],
            [
                'id' => 4,
                'title' => '云南大理洱海休闲游',
                'cover_image' => 'https://images.unsplash.com/photo-1555217851-6141ab127fa8?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '4天3晚·悠闲行',
                'cost' => '人均2.5k',
                'user' => [
                    'name' => '慢游者',
                    'avatar' => 'https://randomuser.me/api/portraits/women/56.jpg'
                ],
                'views' => 1879,
                'likes' => 402,
                'comments' => 67
            ],
            [
                'id' => 5,
                'title' => '东京美食探索',
                'cover_image' => 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '6天·人均5k',
                'user' => [
                    'name' => '吃货旅行家',
                    'avatar' => 'https://randomuser.me/api/portraits/men/22.jpg'
                ],
                'views' => 4210,
                'likes' => 893,
                'comments' => 156
            ],
            [
                'id' => 6,
                'title' => '泰国清迈小众玩法',
                'cover_image' => 'https://images.unsplash.com/photo-1528181304800-259b08848526?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '5天·小众探索',
                'user' => [
                    'name' => '探索者',
                    'avatar' => 'https://randomuser.me/api/portraits/men/76.jpg'
                ],
                'views' => 1567,
                'likes' => 328,
                'comments' => 48
            ],
            [
                'id' => 7,
                'title' => '三亚亲子度假攻略',
                'cover_image' => 'https://images.unsplash.com/photo-1540202404-a2f29016b523?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '6天5晚·亲子游',
                'cost' => '家庭6k',
                'user' => [
                    'name' => '快乐妈咪',
                    'avatar' => 'https://randomuser.me/api/portraits/women/65.jpg'
                ],
                'views' => 3245,
                'likes' => 714,
                'comments' => 129
            ],
            [
                'id' => 8,
                'title' => '西藏拉萨朝圣之旅',
                'cover_image' => 'https://images.unsplash.com/photo-1461823385004-d7660947a7c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '8天·心灵之旅',
                'user' => [
                    'name' => '高原行者',
                    'avatar' => 'https://randomuser.me/api/portraits/men/42.jpg'
                ],
                'views' => 2789,
                'likes' => 651,
                'comments' => 94
            ]
        ];
    }
} 