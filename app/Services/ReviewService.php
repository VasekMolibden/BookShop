<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    /**
     * Paginate reviews for a book (newest first).
     */
    public function paginateForBook(Book $book, int $perPage = 15): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 100);

        return Review::query()
            ->where('book_id', $book->id)
            ->with(['user:id,name'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Paginate reviews for a user (newest first).
     */
    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 100);

        return Review::query()
            ->where('user_id', $user->id)
            ->with(['book:id,title,author,author_id', 'book.author:id,name,slug'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Create a review (one per user per book).
     *
     * @param  array{rating: int, comment: string|null}  $data
     *
     * @throws ValidationException
     */
    public function create(User $user, Book $book, array $data): Review
    {
        if (Review::query()->where('user_id', $user->id)->where('book_id', $book->id)->exists()) {
            throw ValidationException::withMessages([
                'book_id' => ['Вы уже оставили отзыв к этой книге.'],
            ]);
        }

        return Review::query()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ])->load(['user:id,name']);
    }

    /**
     * Update an existing review.
     *
     * @param  array{rating: int, comment: string|null}  $data
     */
    public function update(User $user, Review $review, array $data): Review
    {
        $this->assertOwnsReview($user, $review);

        $review->fill([
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);
        $review->save();

        return $review->fresh(['user:id,name']);
    }

    /**
     * Delete a review.
     */
    public function delete(User $user, Review $review): void
    {
        $this->assertOwnsReview($user, $review);
        $review->delete();
    }

    private function assertOwnsReview(User $user, Review $review): void
    {
        if ($review->user_id !== $user->id) {
            abort(404);
        }
    }
}
