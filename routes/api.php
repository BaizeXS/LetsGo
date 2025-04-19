<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\HomeController;

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

// Post routes
Route::post('/posts/{id}/comments', [CommentController::class, 'store']);

// Comment routes
Route::post('/comments/{id}/like', [CommentController::class, 'toggleLike']);
Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

// Generate route map
Route::post('/generate-route-map', [PostController::class, 'generateRouteMap'])->name('generate.route.map');

// Search routes
Route::get('/search', [HomeController::class, 'apiSearch'])->name('api.search');

// Get posts (for infinite loading)
Route::get('/posts', [HomeController::class, 'getPosts'])->name('api.posts'); 