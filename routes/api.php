<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 帖子路由
Route::post('/posts/{id}/comments', [CommentController::class, 'store']);

// 评论路由
Route::post('/comments/{id}/like', [CommentController::class, 'toggleLike']);
Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

// 生成路线图
Route::post('/generate-route-map', [PostController::class, 'generateRouteMap'])->name('generate.route.map'); 