<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 显示用户个人资料
     */
    public function profile()
    {
        // 在实际应用中，这里应该获取当前登录用户的信息
        $user = [
            'id' => 1,
            'name' => '旅行达人',
            'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg',
            'bio' => '热爱旅行，足迹已遍布30多个国家和地区。擅长分享实用的旅行攻略和旅途中的美好记忆。',
            'posts_count' => 24,
            'followers_count' => 1280,
            'following_count' => 325
        ];
        
        // 获取用户的帖子
        $posts = [
            [
                'id' => 1,
                'title' => '北海道7日游',
                'cover_image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '7天6晚·12.24',
                'views' => 1245,
                'likes' => 324,
                'comments' => 56
            ],
            [
                'id' => 5,
                'title' => '东京美食探索',
                'cover_image' => 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '6天·人均5k',
                'views' => 4210,
                'likes' => 893,
                'comments' => 156
            ]
        ];
        
        return view('user.profile', ['user' => $user, 'posts' => $posts]);
    }
    
    /**
     * 显示编辑个人资料表单
     */
    public function edit()
    {
        // 在实际应用中，这里应该获取当前登录用户的信息
        $user = [
            'id' => 1,
            'name' => '旅行达人',
            'email' => 'travel@example.com',
            'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg',
            'bio' => '热爱旅行，足迹已遍布30多个国家和地区。擅长分享实用的旅行攻略和旅途中的美好记忆。'
        ];
        
        return view('user.edit', ['user' => $user]);
    }
    
    /**
     * 更新用户个人资料
     */
    public function update(Request $request)
    {
        // 验证请求
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'avatar' => 'nullable|image|max:1024',
            'bio' => 'nullable|max:1000',
        ]);
        
        // 处理头像上传
        // if ($request->hasFile('avatar')) {
        //     $avatar = $request->file('avatar')->store('avatars');
        // }
        
        // 更新用户信息
        // $user = Auth::user();
        // $user->update([...]);
        
        return redirect()->route('user.profile')->with('success', '个人资料更新成功！');
    }
    
    /**
     * 显示用户收藏的帖子
     */
    public function favorites()
    {
        // 获取用户收藏的帖子
        $favorites = [
            [
                'id' => 2,
                'title' => '新疆独库公路',
                'cover_image' => 'https://images.unsplash.com/photo-1494783367193-149034c05e8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'duration' => '5天·人均4k',
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
            ]
        ];
        
        return view('user.favorites', ['favorites' => $favorites]);
    }
} 