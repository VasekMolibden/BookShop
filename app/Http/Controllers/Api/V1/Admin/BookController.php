<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreBookRequest;
use App\Http\Requests\Api\V1\Admin\UpdateBookRequest;
use App\Http\Resources\Api\V1\BookResource;
use App\Models\Book;
use App\Services\Admin\AdminBookService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    public function __construct(
        private readonly AdminBookService $bookService
    ) {}

    /**
     * Create a book.
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = $this->bookService->create($request->validated());
        $book->loadCount('reviews');

        return ApiResponse::success([
            'book' => new BookResource($book),
        ], 'Книга создана', 201);
    }

    /**
     * Update a book.
     */
    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        $book = $this->bookService->update($book, $request->validated());
        $book->loadCount('reviews');

        return ApiResponse::success([
            'book' => new BookResource($book),
        ], 'Книга обновлена');
    }

    /**
     * Delete a book.
     */
    public function destroy(Book $book): JsonResponse
    {
        $this->bookService->delete($book);

        return ApiResponse::success(null, 'Книга удалена');
    }
}
