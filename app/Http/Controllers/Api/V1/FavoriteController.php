<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AddFavoriteRequest;
use App\Http\Requests\Api\V1\FavoriteIndexRequest;
use App\Http\Resources\Api\V1\FavoriteResource;
use App\Models\Book;
use App\Services\FavoriteService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(
        private readonly FavoriteService $favoriteService
    ) {}

    /**
     * List favorite books for the authenticated user.
     */
    public function index(FavoriteIndexRequest $request): JsonResponse
    {
        $perPage = (int) ($request->validated('per_page') ?? 15);
        $paginator = $this->favoriteService->paginateForUser($request->user(), $perPage);

        return ApiResponse::success([
            'favorites' => FavoriteResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 'Избранное');
    }

    /**
     * Add a book to favorites.
     */
    public function store(AddFavoriteRequest $request): JsonResponse
    {
        $book = Book::query()->findOrFail((int) $request->validated('book_id'));
        $favorite = $this->favoriteService->add($request->user(), $book);

        $status = $favorite->wasRecentlyCreated ? 201 : 200;
        $message = $favorite->wasRecentlyCreated
            ? 'Книга добавлена в избранное'
            : 'Книга уже в избранном';

        return ApiResponse::success([
            'favorite' => new FavoriteResource($favorite),
        ], $message, $status);
    }

    /**
     * Remove a book from favorites (by book id or slug in the route).
     */
    public function destroy(Request $request, Book $book): JsonResponse
    {
        $this->favoriteService->remove($request->user(), $book);

        return ApiResponse::success(null, 'Книга удалена из избранного');
    }
}
