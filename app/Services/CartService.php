<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * Get the user's cart with items and book details (creates an empty cart if missing).
     */
    public function getCart(User $user): Cart
    {
        $cart = $this->getOrCreateCart($user);
        $cart->load(['items.book.genres', 'items.book.author']);

        return $cart;
    }

    /**
     * Add a book to the cart or increase quantity if the line already exists.
     *
     * @throws ValidationException
     */
    public function addItem(User $user, int $bookId, int $quantity): CartItem
    {
        $book = Book::query()->findOrFail($bookId);

        if ($book->stock < 1) {
            throw ValidationException::withMessages([
                'book_id' => ['Товар недоступен.'],
            ]);
        }

        $cart = $this->getOrCreateCart($user);
        $existing = $cart->items()->where('book_id', $bookId)->first();
        $newQuantity = ($existing?->quantity ?? 0) + $quantity;

        if ($newQuantity > $book->stock) {
            throw ValidationException::withMessages([
                'quantity' => ['Недостаточно товара на складе.'],
            ]);
        }

        if ($existing) {
            $existing->quantity = $newQuantity;
            $existing->save();

            return $existing->load(['book.genres', 'book.author']);
        }

        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'book_id' => $bookId,
            'quantity' => $quantity,
        ])->load(['book.genres', 'book.author']);
    }

    /**
     * Set quantity for a cart line.
     *
     * @throws ValidationException
     */
    public function updateItemQuantity(User $user, CartItem $item, int $quantity): CartItem
    {
        $this->assertOwnsCartItem($user, $item);

        $book = Book::query()->findOrFail($item->book_id);

        if ($quantity > $book->stock) {
            throw ValidationException::withMessages([
                'quantity' => ['Недостаточно товара на складе.'],
            ]);
        }

        $item->quantity = $quantity;
        $item->save();

        return $item->fresh(['book.genres', 'book.author']);
    }

    /**
     * Remove a line from the cart.
     */
    public function removeItem(User $user, CartItem $item): void
    {
        $this->assertOwnsCartItem($user, $item);
        $item->delete();
    }

    /**
     * Ensure the cart exists for the user.
     */
    public function getOrCreateCart(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Ensure the cart item belongs to the authenticated user.
     */
    private function assertOwnsCartItem(User $user, CartItem $item): void
    {
        $item->loadMissing('cart');

        if (! $item->cart || $item->cart->user_id !== $user->id) {
            abort(404);
        }
    }
}
