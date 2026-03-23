<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Book
 */
class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'author' => $this->author,
            'description' => $this->description,
            'price' => $this->price,
            'isbn' => $this->isbn,
            'stock' => $this->stock,
            'published_at' => $this->published_at?->toDateString(),
            'reviews_count' => $this->whenCounted('reviews'),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'images' => BookImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
