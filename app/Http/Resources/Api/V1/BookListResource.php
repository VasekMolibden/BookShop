<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Book
 */
class BookListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $primaryImage = $this->images->firstWhere('is_primary', true) ?? $this->images->first();

        // Get author name from database field or relation
        $authorName = $this->getRawOriginal('author');
        if (!$authorName && $this->relationLoaded('author') && $this->author) {
            $authorName = $this->author->name;
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'author' => $authorName ?: 'Неизвестный автор',
            'author_data' => $this->when($this->relationLoaded('author'), AuthorResource::make($this->getRelation('author'))),
            'price' => $this->price,
            'stock' => $this->stock,
            'published_at' => $this->published_at?->toDateString(),
            'reviews_count' => $this->whenCounted('reviews'),
            'average_rating' => $this->when($this->reviews_count > 0, round($this->average_rating, 1)),
            'genres' => GenreResource::collection($this->genres),
            'primary_image' => new BookImageResource($primaryImage),
        ];
    }
}
