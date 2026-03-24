<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title',
    'slug',
    'author',
    'author_id',
    'description',
    'price',
    'isbn',
    'stock',
    'published_at',
])]
class Book extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'published_at' => 'date',
        ];
    }

    /**
     * Genres associated with this book.
     *
     * @return BelongsToMany<Genre, $this>
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    /**
     * Reviews for this book.
     *
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Favorites referencing this book.
     *
     * @return HasMany<Favorite, $this>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Images for this book.
     *
     * @return HasMany<BookImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(BookImage::class)->orderBy('sort_order');
    }

    /**
     * Author of this book.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Author, $this>
     */
    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * Get the average rating for this book.
     */
    public function getAverageRatingAttribute(): ?float
    {
        return $this->reviews()->avg('rating');
    }
}
