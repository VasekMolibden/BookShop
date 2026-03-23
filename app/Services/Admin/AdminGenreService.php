<?php

namespace App\Services\Admin;

use App\Models\Genre;
use Illuminate\Validation\ValidationException;

class AdminGenreService
{
    /**
     * Create a genre.
     *
     * @param  array{name: string, slug: string}  $data
     */
    public function create(array $data): Genre
    {
        return Genre::query()->create($data);
    }

    /**
     * Update a genre.
     *
     * @param  array{name?: string, slug?: string}  $data
     */
    public function update(Genre $genre, array $data): Genre
    {
        $genre->fill($data);
        $genre->save();

        return $genre->fresh();
    }

    /**
     * Delete a genre if no books are linked.
     *
     * @throws ValidationException
     */
    public function delete(Genre $genre): void
    {
        if ($genre->books()->exists()) {
            throw ValidationException::withMessages([
                'genre' => ['Нельзя удалить жанр, к которому привязаны книги.'],
            ]);
        }

        $genre->delete();
    }
}
