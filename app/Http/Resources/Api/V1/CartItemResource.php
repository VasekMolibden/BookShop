<?php

namespace App\Http\Resources\Api\V1;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CartItem
 */
class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $book = $this->book;
        $lineTotal = round((float) $book->price * (int) $this->quantity, 2);

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'line_total' => $lineTotal,
            'book' => new BookListResource($book),
        ];
    }
}
