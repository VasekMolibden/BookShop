<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Serves Blade shells for the storefront; data is loaded in the browser via the JSON API.
 */
class ShopController extends Controller
{
    /**
     * Catalog listing (filters and pagination are client-side via API).
     */
    public function index(): View
    {
        return view('shop.index');
    }

    /**
     * Single book page (details and reviews loaded via API).
     */
    public function book(Book $book): View
    {
        return view('shop.book', ['bookId' => $book->id]);
    }

    /**
     * Login form (token exchange via API).
     */
    public function login(): View
    {
        return view('shop.login');
    }

    /**
     * Registration form (token issued via API).
     */
    public function register(): View
    {
        return view('shop.register');
    }

    /**
     * Request password reset email (handled via API).
     */
    public function forgotPassword(): View
    {
        return view('shop.forgot-password');
    }

    /**
     * Set a new password using the token from the email link.
     */
    public function resetPassword(Request $request, string $token): View
    {
        return view('shop.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    /**
     * Profile and password settings (Bearer token in the browser).
     */
    public function settings(): View
    {
        return view('shop.settings');
    }

    /**
     * Shopping cart (requires Bearer token on the client).
     */
    public function cart(): View
    {
        return view('shop.cart');
    }

    /**
     * Order history (requires Bearer token on the client).
     */
    public function orders(): View
    {
        return view('shop.orders');
    }

    /**
     * Single order details (requires Bearer token on the client).
     */
    public function order(Order $order): View
    {
        // Order data is loaded via API, just pass the order ID
        return view('shop.order', ['orderId' => $order->id]);
    }

    /**
     * Favorite books (requires Bearer token on the client).
     */
    public function favorites(): View
    {
        return view('shop.favorites');
    }

    /**
     * User reviews (requires Bearer token on the client).
     */
    public function reviews(): View
    {
        return view('shop.reviews');
    }
}
