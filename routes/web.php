<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CommentController;

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

// Test route - directly return content
Route::get('/test', function() {
    return '<h1>Test route is working</h1>';
});

// Homepage route
Route::get('/', [HomeController::class, 'index'])->name('home');

// Search route
Route::get('/search', [HomeController::class, 'search'])->name('search');

// Map related routes
Route::get('/api/posts/location', [HomeController::class, 'getPostsByLocation'])->name('posts.location');

// AI generated route map routes
Route::post('/api/generate-route-map', [PostController::class, 'generateRouteMap'])->name('generate.route.map');

// Post related routes
Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
Route::post('/posts/{id}/favorite', [PostController::class, 'toggleFavorite'])->name('posts.favorite');
Route::post('/posts/{id}/like', [PostController::class, 'toggleLike'])->name('posts.like');

// Comment related routes
Route::get('/posts/{postId}/comments', [CommentController::class, 'index'])->name('comments.index');
Route::middleware(['mock.auth'])->group(function () {
    Route::post('/posts/{postId}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{id}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{id}/like', [CommentController::class, 'toggleLike'])->name('comments.like');
});

// Profile center redirect - redirect old profile route to favorites
Route::redirect('/profile', '/favorites')->name('user.profile');

// Routes requiring authentication
Route::middleware(['mock.auth'])->group(function () {
    // Create new post
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    
    // Profile editing
    Route::get('/profile/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::put('/profile', [UserController::class, 'update'])->name('user.update');
    Route::post('/posts/{id}/pin', [UserController::class, 'togglePinPost'])->name('posts.pin');
    
    // My favorites
    Route::get('/favorites', [UserController::class, 'favorites'])->name('user.favorites');
    
    // Following system
    Route::post('/users/{id}/follow', [UserController::class, 'toggleFollow'])->name('users.follow');
    Route::get('/followers', [UserController::class, 'followers'])->name('user.followers');
    Route::get('/following', [UserController::class, 'following'])->name('user.following');
    
    // My posts route
    Route::get('user/my/posts', [UserController::class, 'myPosts'])->name('user.my.posts');
});

// Public profile routes
Route::get('/@{username}', [UserController::class, 'profile'])->name('users.profile');
Route::get('/@{username}/followers', [UserController::class, 'followers'])->name('users.followers');
Route::get('/@{username}/following', [UserController::class, 'following'])->name('users.following');

// Authentication routes
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// These routes have been moved inside the authenticated middleware group
// Route::get('user/my/posts', [UserController::class, 'myPosts'])->name('user.my.posts');
// Route::get('user/{username}/following', [UserController::class, 'following'])->name('user.following');
// Route::get('user/following', [UserController::class, 'following'])->name('user.my.following');
