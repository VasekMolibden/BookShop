<?php

use App\Http\Controllers\Web\AdminPageController;
use App\Http\Controllers\Web\ShopController;
use App\Http\Controllers\Shop\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminPageController::class, 'dashboard'])->name('dashboard');
    Route::get('/genres', [AdminPageController::class, 'genres'])->name('genres');
    Route::get('/authors', [AdminPageController::class, 'authors'])->name('authors');
    Route::get('/books', [AdminPageController::class, 'books'])->name('books');
    Route::get('/books/new', [AdminPageController::class, 'bookCreate'])->name('books.create');
    Route::get('/books/{book}/edit', [AdminPageController::class, 'bookEdit'])->name('books.edit');
    Route::get('/orders', [AdminPageController::class, 'orders'])->name('orders');
    Route::get('/reviews', [AdminPageController::class, 'reviews'])->name('reviews');
    Route::get('/users', [AdminPageController::class, 'users'])->name('users');
});

Route::get('/', [ShopController::class, 'index'])->name('shop.index');
Route::get('/search', [SearchController::class, 'index'])->name('shop.search');
Route::get('/books/{book}', [ShopController::class, 'book'])->name('shop.book');
Route::get('/login', [ShopController::class, 'login'])->name('shop.login');
Route::get('/register', [ShopController::class, 'register'])->name('shop.register');
Route::get('/forgot-password', [ShopController::class, 'forgotPassword'])->name('password.request');
Route::get('/reset-password/{token}', [ShopController::class, 'resetPassword'])->name('password.reset');
Route::get('/settings', [ShopController::class, 'settings'])->name('shop.settings');
Route::get('/cart', [ShopController::class, 'cart'])->name('shop.cart');
Route::get('/orders', [ShopController::class, 'orders'])->name('shop.orders');
Route::get('/orders/{order}', [ShopController::class, 'order'])->name('shop.order');
Route::get('/favorites', [ShopController::class, 'favorites'])->name('shop.favorites');
Route::get('/reviews', [ShopController::class, 'reviews'])->name('shop.reviews');
