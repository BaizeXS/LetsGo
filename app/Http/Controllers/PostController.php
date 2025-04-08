<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * 显示单个帖子详情
     */
    public function show($id)
    {
        // 在实际应用中，这里应该从数据库获取帖子信息
        $post = [
            'id' => $id,
            'title' => '北海道7日游行程攻略',
            'content' => '这是一篇详细介绍北海道7日游的攻略...',
            'cover_image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
            'images' => [
                'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1494783367193-149034c05e8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
            ],
            'duration' => '7天6晚',
            'date' => '12月24日出发',
            'cost' => '人均1.2万',
            'user' => [
                'id' => 1,
                'name' => '旅行达人',
                'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg'
            ],
            'views' => 1245,
            'likes' => 324,
            'comments' => [
                [
                    'id' => 1,
                    'user' => [
                        'name' => '小明',
                        'avatar' => 'https://randomuser.me/api/portraits/men/32.jpg'
                    ],
                    'content' => '太棒了，我也想去！',
                    'created_at' => '2小时前'
                ],
                [
                    'id' => 2,
                    'user' => [
                        'name' => '旅游爱好者',
                        'avatar' => 'https://randomuser.me/api/portraits/women/28.jpg'
                    ],
                    'content' => '请问冬天去北海道需要准备什么衣物呢？',
                    'created_at' => '3小时前'
                ]
            ],
            'created_at' => '2023-12-15'
        ];
        
        return view('posts.show', ['post' => $post]);
    }
    
    /**
     * 显示创建帖子表单
     */
    public function create()
    {
        return view('posts.create');
    }
    
    /**
     * 存储新创建的帖子
     */
    public function store(Request $request)
    {
        // 验证请求
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'cover_image' => 'required|image|max:2048',
            'images.*' => 'image|max:2048',
            'duration' => 'required|max:50',
            'cost' => 'nullable|max:50',
        ]);
        
        // 处理图片上传
        // $coverPath = $request->file('cover_image')->store('posts');
        
        // 保存帖子信息到数据库
        // Post::create([...]);
        
        return redirect()->route('home')->with('success', '帖子发布成功！');
    }
    
    /**
     * 切换收藏状态
     */
    public function toggleFavorite($id)
    {
        // 在实际应用中，这里应该保存/删除用户收藏记录
        return response()->json(['success' => true]);
    }
    
    /**
     * 切换点赞状态
     */
    public function toggleLike($id)
    {
        // 在实际应用中，这里应该保存/删除用户点赞记录
        return response()->json(['success' => true]);
    }
} 