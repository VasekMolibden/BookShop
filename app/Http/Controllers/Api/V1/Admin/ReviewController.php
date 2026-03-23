<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\AdminReviewListRequest;
use App\Http\Resources\Api\V1\ReviewResource;
use App\Models\Review;
use App\Services\Admin\AdminReviewService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function __construct(
        private readonly AdminReviewService $reviewService
    ) {}

    /**
     * Paginated list of all reviews (moderation).
     */
    public function index(AdminReviewListRequest $request): JsonResponse
    {
        $perPage = (int) ($request->validated('per_page') ?? 15);
        $paginator = $this->reviewService->paginateAll($perPage);

        return ApiResponse::success([
            'reviews' => ReviewResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 'Отзывы (админ)');
    }

    /**
     * Delete any review (moderation).
     */
    public function destroy(Review $review): JsonResponse
    {
        $this->reviewService->delete($review);

        return ApiResponse::success(null, 'Отзыв удалён');
    }
}
