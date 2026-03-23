<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AddCartItemRequest;
use App\Http\Requests\Api\V1\UpdateCartItemRequest;
use App\Http\Resources\Api\V1\CartResource;
use App\Models\CartItem;
use App\Services\CartService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    /**
     * Return the current user's cart with line items and totals.
     */
    public function show(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request->user());

        return ApiResponse::success([
            'cart' => new CartResource($cart),
        ], 'Корзина');
    }

    /**
     * Add a book to the cart or merge quantity if the line already exists.
     */
    public function storeItem(AddCartItemRequest $request): JsonResponse
    {
        $this->cartService->addItem(
            $request->user(),
            (int) $request->validated('book_id'),
            (int) $request->validated('quantity')
        );

        $cart = $this->cartService->getCart($request->user());

        return ApiResponse::success([
            'cart' => new CartResource($cart),
        ], 'Товар добавлен в корзину', 201);
    }

    /**
     * Update quantity for a cart line.
     */
    public function updateItem(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $this->cartService->updateItemQuantity(
            $request->user(),
            $cartItem,
            (int) $request->validated('quantity')
        );

        $cart = $this->cartService->getCart($request->user());

        return ApiResponse::success([
            'cart' => new CartResource($cart),
        ], 'Количество обновлено');
    }

    /**
     * Remove a line from the cart.
     */
    public function destroyItem(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->cartService->removeItem($request->user(), $cartItem);

        $cart = $this->cartService->getCart($request->user());

        return ApiResponse::success([
            'cart' => new CartResource($cart),
        ], 'Позиция удалена из корзины');
    }
}
