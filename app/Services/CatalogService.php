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
     * Paginated books with optional filters and sorting.
     *
     * @param  array{
     *     per_page?: int,
     *     genre?: string|null,
     *     author?: string|null,
     *     search?: string|null,
     *     year?: int|null,
     *     min_price?: float|null,
     *     max_price?: float|null,
     *     min_rating?: float|null,
     *     in_stock?: bool|null,
     *     published_after?: string|null,
     *     published_before?: string|null,
     *     sort?: string|null
     * }  $filters
     */
    public function paginateBooks(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), 100);

        $query = Book::query()
            ->with(['genres:id,name,slug', 'author:id,name,slug'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        // Genre filter
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

        // Author filter
        if (! empty($filters['author'])) {
            $author = $filters['author'];
            $query->whereHas('author', function (Builder $q) use ($author) {
                if (is_numeric($author)) {
                    $q->where('authors.id', (int) $author);
                } else {
                    $q->where('authors.slug', $author);
                }
            });
        }

        // Search filter
        if (! empty($filters['search'])) {
            $searchTerm = trim((string) $filters['search']);
            if (strlen($searchTerm) >= 2) { // Minimum 2 characters
                // Check for exact phrases in quotes
                $exactPhrases = [];
                $remainingTerm = preg_replace_callback('/"([^"]+)"/', function ($matches) use (&$exactPhrases) {
                    $exactPhrases[] = $matches[1];
                    return '';
                }, $searchTerm);
                
                // Split remaining term into words
                $words = array_slice(array_filter(explode(' ', trim($remainingTerm))), 0, 5);
                
                $query->where(function (Builder $q) use ($words, $exactPhrases) {
                    // Exact phrase search (highest priority)
                    foreach ($exactPhrases as $phrase) {
                        if (strlen($phrase) >= 2) {
                            $escaped = addcslashes(strtolower($phrase), '%_\\');
                            $q->orWhere(function (Builder $subQ) use ($escaped) {
                                $subQ->whereRaw('LOWER(title) LIKE LOWER(?)', ['%'.$escaped.'%'])
                                    ->orWhereRaw('LOWER(description) LIKE LOWER(?)', ['%'.$escaped.'%']);
                            });
                        }
                    }
                    
                    // Word-based search
                    foreach ($words as $word) {
                        if (strlen($word) >= 2) { // Skip very short words
                            $escaped = addcslashes(strtolower($word), '%_\\');
                            $term = '%'.$escaped.'%';
                            
                            $q->orWhere(function (Builder $subQ) use ($term) {
                                // Search in title (case-insensitive) - highest priority
                                $subQ->whereRaw('LOWER(title) LIKE LOWER(?)', [$term])
                                    // Search in author field (case-insensitive)  
                                    ->orWhereRaw('LOWER(author) LIKE LOWER(?)', [$term])
                                    // Search in author name via relation (case-insensitive)
                                    ->orWhereHas('author', function (Builder $authorQ) use ($term) {
                                        $authorQ->whereRaw('LOWER(name) LIKE LOWER(?)', [$term]);
                                    })
                                    // Search in description (case-insensitive)
                                    ->orWhereRaw('LOWER(description) LIKE LOWER(?)', [$term])
                                    // Search in ISBN
                                    ->orWhereRaw('LOWER(isbn) LIKE LOWER(?)', [$term])
                                    // Search in genres
                                    ->orWhereHas('genres', function (Builder $genreQ) use ($term) {
                                        $genreQ->whereRaw('LOWER(name) LIKE LOWER(?)', [$term]);
                                    });
                            });
                        }
                    }
                });
            }
        }

        // Price filters
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $query->where('price', '>=', (float) $filters['min_price']);
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        // Rating filter
        if (isset($filters['min_rating']) && is_numeric($filters['min_rating'])) {
            $minRating = (float) $filters['min_rating'];
            $query->whereRaw('(SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE book_id = books.id) >= ?', [$minRating]);
        }

        // Stock filter
        if (! empty($filters['in_stock'])) {
            $query->where('stock', '>', 0);
        }

        // Publication date filters
        if (! empty($filters['published_after'])) {
            $query->where('published_at', '>=', $filters['published_after']);
        }
        if (! empty($filters['published_before'])) {
            $query->where('published_at', '<=', $filters['published_before']);
        }

        // Publication year filter (for search)
        if (! empty($filters['year'])) {
            $year = (int) $filters['year'];
            if ($year >= 1800 && $year <= date('Y') + 1) {
                $query->whereYear('published_at', $year);
            }
        }

        // Sorting
        $sort = $filters['sort'] ?? 'title';
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating_desc':
                $query->orderBy('reviews_avg_rating', 'desc');
                break;
            case 'reviews_desc':
                $query->orderBy('reviews_count', 'desc');
                break;
            case 'newest':
                $query->orderBy('published_at', 'desc');
                break;
            case 'popularity':
                // For now, sort by reviews count as popularity proxy
                $query->orderBy('reviews_count', 'desc');
                break;
            default:
                $query->orderBy('title');
        }

        return $query->paginate($perPage);
    }
}
