<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 测试路由 - 直接返回内容
Route::get('/test', function() {
    return '<h1>测试路由正常</h1>';
});

// 首页路由
Route::get('/', [HomeController::class, 'index'])->name('home');

// 帖子相关路由
Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
Route::post('/posts/{id}/favorite', [PostController::class, 'toggleFavorite'])->name('posts.favorite');
Route::post('/posts/{id}/like', [PostController::class, 'toggleLike'])->name('posts.like');

// 需要认证的路由
Route::middleware(['auth'])->group(function () {
    // 发布新帖子
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    
    // 个人中心
    Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::get('/profile/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::put('/profile', [UserController::class, 'update'])->name('user.update');
    
    // 我的收藏
    Route::get('/favorites', [UserController::class, 'favorites'])->name('user.favorites');
});

// 认证路由
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
