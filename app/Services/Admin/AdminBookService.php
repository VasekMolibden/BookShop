<?php

namespace App\Services\Admin;

use App\Models\Book;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminBookService
{
    /**
     * Create a book and attach genres.
     *
     * @param  array<string, mixed>  $data  Must include genre_ids (array<int>)
     */
    public function create(array $data): Book
    {
        $genreIds = $data['genre_ids'];
        $attributes = Arr::except($data, ['genre_ids']);

        if (empty($attributes['slug'])) {
            $attributes['slug'] = $this->uniqueSlugFromTitle((string) $attributes['title']);
        }

        $book = Book::query()->create($attributes);
        $book->genres()->sync($genreIds);

        return $book->load(['genres:id,name,slug']);
    }

    /**
     * Update a book and optionally sync genres.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Book $book, array $data): Book
    {
        $genreIds = Arr::pull($data, 'genre_ids');

        if (array_key_exists('slug', $data) && ($data['slug'] === null || $data['slug'] === '')) {
            $data['slug'] = $this->uniqueSlugFromTitle(
                (string) ($data['title'] ?? $book->title),
                $book->id
            );
        }

        $book->fill($data);
        $book->save();

        if (is_array($genreIds)) {
            $book->genres()->sync($genreIds);
        }

        return $book->fresh(['genres:id,name,slug']);
    }

    /**
     * Delete a book (related pivot rows removed by FK).
     */
    public function delete(Book $book): void
    {
        $book->delete();
    }

    /**
     * Build a unique slug for books from a title string.
     */
    private function uniqueSlugFromTitle(string $title, ?int $exceptBookId = null): string
    {
        $base = Str::slug($title) ?: 'book';
        $slug = $base;
        $i = 1;

        while (Book::query()
            ->where('slug', $slug)
            ->when($exceptBookId !== null, fn ($q) => $q->where('id', '!=', $exceptBookId))
            ->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
