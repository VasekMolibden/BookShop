<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookIndexRequest;
use App\Http\Resources\Api\V1\BookListResource;
use App\Http\Resources\Api\V1\BookResource;
use App\Models\Book;
use App\Services\CatalogService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalogService
    ) {}

    /**
     * Paginated catalog of books.
     */
    public function index(BookIndexRequest $request): JsonResponse
    {
        $paginator = $this->catalogService->paginateBooks($request->validated());

        return ApiResponse::success([
            'books' => BookListResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 'Каталог книг');
    }

    /**
     * Single book details (resolve by numeric id or slug).
     */
    public function show(Book $book): JsonResponse
    {
        $book->load(['genres:id,name,slug', 'images', 'author:id,name,slug']);
        $book->loadCount('reviews');

        return ApiResponse::success([
            'book' => new BookResource($book),
        ], 'Книга');
    }
}
