<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['index']);
    }
    
    /**
     * Display a listing of the comments for a post.
     *
     * @param  int  $postId
     * @return \Illuminate\Http\Response
     */
    public function index($postId)
    {
        $post = Post::findOrFail($postId);
        $comments = $post->comments()
            ->with('user')
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'comments' => $comments,
            'total' => $comments->count()
        ]);
    }

    /**
     * Store a new comment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $postId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $post = Post::findOrFail($postId);
        
        $comment = new Comment([
            'content' => $request->content,
            'user_id' => Auth::id(),
            'post_id' => $post->id,
            'parent_id' => $request->parent_id
        ]);
        
        $comment->save();
        
        // Load the user relationship for the response
        $comment->load('user');
        
        return response()->json([
            'success' => true,
            'message' => 'Comment created successfully',
            'comment' => $comment
        ], 201);
    }

    /**
     * Update the specified comment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        
        // Check if the user is authorized to update the comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);
        
        $comment->content = $request->content;
        $comment->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'comment' => $comment
        ]);
    }

    /**
     * Remove the specified comment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        
        // Check if the user is authorized to delete the comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $comment->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }
    
    /**
     * Like/unlike a comment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleLike($id)
    {
        $comment = Comment::findOrFail($id);
        
        // In a real application, you would use a pivot table for likes
        // This is a simplified implementation
        $comment->likes = $comment->likes + 1;
        $comment->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Comment liked successfully',
            'likes' => $comment->likes
        ]);
    }
}
