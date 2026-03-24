<?php

use App\Http\Controllers\Api\V1\Admin\AuthorController as AdminAuthorController;
use App\Http\Controllers\Api\V1\Admin\BookController as AdminBookController;
use App\Http\Controllers\Api\V1\Admin\BookImageController as AdminBookImageController;
use App\Http\Controllers\Api\V1\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'index']);

Route::get('/genres', [GenreController::class, 'index']);
Route::get('/authors', [AdminAuthorController::class, 'index']);
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{book}/reviews', [ReviewController::class, 'index']);
Route::get('/books/{book}', [BookController::class, 'show']);

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::patch('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::patch('/auth/password', [AuthController::class, 'updatePassword']);

    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart/items', [CartController::class, 'storeItem']);
    Route::patch('/cart/items/{cartItem}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroyItem']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{book}', [FavoriteController::class, 'destroy']);

    Route::get('/reviews', [ReviewController::class, 'userReviews']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::patch('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    Route::middleware('admin')->prefix('admin')->group(function (): void {
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::patch('/users/{user}', [AdminUserController::class, 'update']);

        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::patch('/orders/{order}', [AdminOrderController::class, 'update']);

        Route::get('/reviews', [AdminReviewController::class, 'index']);
        Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy']);

        Route::get('/authors', [AdminAuthorController::class, 'index']);
        Route::post('/authors', [AdminAuthorController::class, 'store']);
        Route::patch('/authors/{author}', [AdminAuthorController::class, 'update']);
        Route::delete('/authors/{author}', [AdminAuthorController::class, 'destroy']);

        Route::post('/genres', [AdminGenreController::class, 'store']);
        Route::patch('/genres/{genre}', [AdminGenreController::class, 'update']);
        Route::delete('/genres/{genre}', [AdminGenreController::class, 'destroy']);

        Route::post('/books', [AdminBookController::class, 'store']);
        Route::patch('/books/{book}', [AdminBookController::class, 'update']);
        Route::delete('/books/{book}', [AdminBookController::class, 'destroy']);

        Route::post('/books/{book}/images', [AdminBookImageController::class, 'store']);
        Route::patch('/books/{book}/images/{image}', [AdminBookImageController::class, 'update']);
        Route::delete('/books/{book}/images/{image}', [AdminBookImageController::class, 'destroy']);
        Route::post('/books/{book}/images/reorder', [AdminBookImageController::class, 'reorder']);
    });
});
