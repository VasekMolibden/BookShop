<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CatalogService
{
    /**
     * All genres ordered by name.
     *
     * @return Collection<int, Genre>
     */
    public function listGenres()
    {
        return Genre::query()->orderBy('name')->get();
    }

    /**
     * Paginated books with optional genre filter and title/author search.
     *
     * @param  array{per_page?: int, genre?: string|null, search?: string|null}  $filters
     */
    public function paginateBooks(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), 100);

        $query = Book::query()->with(['genres:id,name,slug']);

        if (! empty($filters['genre'])) {
            $genre = $filters['genre'];
            $query->whereHas('genres', function (Builder $q) use ($genre) {
                if (is_numeric($genre)) {
                    $q->where('genres.id', (int) $genre);
                } else {
                    $q->where('genres.slug', $genre);
                }
            });
        }

        if (! empty($filters['search'])) {
            $escaped = addcslashes((string) $filters['search'], '%_\\');
            $term = '%'.$escaped.'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('author', 'like', $term);
            });
        }

        return $query->orderBy('title')->paginate($perPage);
    }
}
