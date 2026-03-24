<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreAuthorRequest;
use App\Http\Requests\Api\V1\Admin\UpdateAuthorRequest;
use App\Http\Resources\Api\V1\AuthorResource;
use App\Models\Author;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthorController extends Controller
{
    /**
     * Get all authors.
     */
    public function index(): JsonResponse
    {
        $authors = Author::withCount('books')->orderBy('name')->get();

        return ApiResponse::success([
            'authors' => AuthorResource::collection($authors),
        ]);
    }

    /**
     * Create an author.
     */
    public function store(StoreAuthorRequest $request): JsonResponse
    {
        $author = Author::create($request->validated());

        return ApiResponse::success([
            'author' => new AuthorResource($author),
        ], 'Автор создан', 201);
    }

    /**
     * Update an author.
     */
    public function update(UpdateAuthorRequest $request, Author $author): JsonResponse
    {
        $author->update($request->validated());

        return ApiResponse::success([
            'author' => new AuthorResource($author),
        ], 'Автор обновлен');
    }

    /**
     * Delete an author.
     */
    public function destroy(Author $author): JsonResponse
    {
        // Check if author has books
        if ($author->books()->exists()) {
            return ApiResponse::error('Нельзя удалить автора, у которого есть книги', 422);
        }

        $author->delete();

        return ApiResponse::success([], 'Автор удален');
    }
}