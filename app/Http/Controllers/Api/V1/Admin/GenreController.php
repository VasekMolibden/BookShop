<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreGenreRequest;
use App\Http\Requests\Api\V1\Admin\UpdateGenreRequest;
use App\Http\Resources\Api\V1\GenreResource;
use App\Models\Genre;
use App\Services\Admin\AdminGenreService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class GenreController extends Controller
{
    public function __construct(
        private readonly AdminGenreService $genreService
    ) {}

    /**
     * Create a genre.
     */
    public function store(StoreGenreRequest $request): JsonResponse
    {
        $genre = $this->genreService->create($request->validated());

        return ApiResponse::success([
            'genre' => new GenreResource($genre),
        ], 'Жанр создан', 201);
    }

    /**
     * Update a genre.
     */
    public function update(UpdateGenreRequest $request, Genre $genre): JsonResponse
    {
        $genre = $this->genreService->update($genre, $request->validated());

        return ApiResponse::success([
            'genre' => new GenreResource($genre),
        ], 'Жанр обновлён');
    }

    /**
     * Delete a genre without linked books.
     */
    public function destroy(Genre $genre): JsonResponse
    {
        $this->genreService->delete($genre);

        return ApiResponse::success(null, 'Жанр удалён');
    }
}
