<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cart
 */
class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = $this->items;
        $totalQuantity = (int) $items->sum('quantity');
        $totalAmount = round($items->sum(function ($item) {
            return (float) $item->book->price * (int) $item->quantity;
        }), 2);

        return [
            'id' => $this->id,
            'items' => CartItemResource::collection($items),
            'total_quantity' => $totalQuantity,
            'total_amount' => $totalAmount,
        ];
    }
}
