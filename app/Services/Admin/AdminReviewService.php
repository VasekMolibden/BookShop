<?php

namespace App\Services\Admin;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminReviewService
{
    /**
     * Paginated list of all reviews for moderation.
     */
    public function paginateAll(int $perPage = 15): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 100);

        return Review::query()
            ->with(['user:id,name', 'book:id,title'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Remove a review (moderation).
     */
    public function delete(Review $review): void
    {
        $review->delete();
    }
}
