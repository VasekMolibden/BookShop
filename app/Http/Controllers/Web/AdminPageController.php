<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Contracts\View\View;

/**
 * Blade shells for the admin panel (data loaded via JSON API with Bearer token).
 */
class AdminPageController extends Controller
{
    /**
     * Admin home with navigation cards.
     */
    public function dashboard(): View
    {
        return view('admin.dashboard');
    }

    /**
     * Genre management.
     */
    public function genres(): View
    {
        return view('admin.genres');
    }

    /**
     * Book list.
     */
    public function books(): View
    {
        return view('admin.books');
    }

    /**
     * Create book form.
     */
    public function bookCreate(): View
    {
        return view('admin.book-form', ['bookId' => null]);
    }

    /**
     * Edit book form.
     */
    public function bookEdit(Book $book): View
    {
        return view('admin.book-form', ['bookId' => $book->id]);
    }

    /**
     * All orders and status changes.
     */
    public function orders(): View
    {
        return view('admin.orders');
    }

    /**
     * Review moderation.
     */
    public function reviews(): View
    {
        return view('admin.reviews');
    }

    /**
     * Registered users and admin roles.
     */
    public function users(): View
    {
        return view('admin.users');
    }
}
