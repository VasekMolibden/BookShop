<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookReviewsIndexRequest;
use App\Http\Requests\Api\V1\StoreReviewRequest;
use App\Http\Requests\Api\V1\UpdateReviewRequest;
use App\Http\Resources\Api\V1\ReviewResource;
use App\Models\Book;
use App\Models\Review;
use App\Services\ReviewService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService
    ) {}

    /**
     * List reviews for a book (public).
     */
    public function index(Book $book, BookReviewsIndexRequest $request): JsonResponse
    {
        $perPage = (int) ($request->validated('per_page') ?? 15);
        $paginator = $this->reviewService->paginateForBook($book, $perPage);

        return ApiResponse::success([
            'reviews' => ReviewResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 'Отзывы о книге');
    }

    /**
     * Create a review for a book.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $book = Book::query()->findOrFail((int) $request->validated('book_id'));
        $review = $this->reviewService->create(
            $request->user(),
            $book,
            $request->safe()->only(['rating', 'comment'])
        );

        return ApiResponse::success([
            'review' => new ReviewResource($review),
        ], 'Отзыв добавлен', 201);
    }

    /**
     * Update the authenticated user's review.
     */
    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        $review = $this->reviewService->update(
            $request->user(),
            $review,
            $request->validated()
        );

        return ApiResponse::success([
            'review' => new ReviewResource($review),
        ], 'Отзыв обновлён');
    }

    /**
     * Delete the authenticated user's review.
     */
    public function destroy(Request $request, Review $review): JsonResponse
    {
        $this->reviewService->delete($request->user(), $review);

        return ApiResponse::success(null, 'Отзыв удалён');
    }
}
