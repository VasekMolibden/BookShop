<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FavoriteService
{
    /**
     * Paginate the user's favorite books (newest first).
     */
    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 100);

        return Favorite::query()
            ->where('user_id', $user->id)
            ->with(['book.genres', 'book.author'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Add a book to favorites (idempotent if already present).
     */
    public function add(User $user, Book $book): Favorite
    {
        $favorite = Favorite::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'book_id' => $book->id,
            ],
        );

        return $favorite->load(['book.genres', 'book.author']);
    }

    /**
     * Remove a book from the user's favorites.
     */
    public function remove(User $user, Book $book): void
    {
        $deleted = Favorite::query()
            ->where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->delete();

        if ($deleted === 0) {
            abort(404);
        }
    }
}
