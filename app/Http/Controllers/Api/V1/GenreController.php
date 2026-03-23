<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\GenreResource;
use App\Services\CatalogService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class GenreController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalogService
    ) {}

    /**
     * List all book genres.
     */
    public function index(): JsonResponse
    {
        $genres = $this->catalogService->listGenres();

        return ApiResponse::success([
            'genres' => GenreResource::collection($genres),
        ], 'Список жанров');
    }
}
