<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Paginate orders for the given user (newest first).
     */
    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 100);

        return Order::query()
            ->where('user_id', $user->id)
            ->with(['items.book.genres'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Create an order from the user's cart, decrease stock, and clear the cart.
     *
     * @throws ValidationException
     */
    public function checkoutFromCart(User $user): Order
    {
        return DB::transaction(function () use ($user) {
            $cart = Cart::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $cart) {
                throw ValidationException::withMessages([
                    'cart' => ['Корзина пуста.'],
                ]);
            }

            $cartItems = CartItem::query()
                ->where('cart_id', $cart->id)
                ->orderBy('book_id')
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Корзина пуста.'],
                ]);
            }

            $lines = [];
            $total = 0.0;

            foreach ($cartItems as $cartItem) {
                $book = Book::query()->whereKey($cartItem->book_id)->lockForUpdate()->firstOrFail();

                if ($book->stock < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => ['Недостаточно товара на складе для оформления заказа.'],
                    ]);
                }

                $unitPrice = round((float) $book->price, 2);
                $subtotal = round($unitPrice * (int) $cartItem->quantity, 2);
                $total += $subtotal;

                $lines[] = [
                    'book' => $book,
                    'quantity' => (int) $cartItem->quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $total = round($total, 2);

            $order = Order::query()->create([
                'user_id' => $user->id,
                'status' => OrderStatus::Pending,
                'total' => $total,
            ]);

            foreach ($lines as $line) {
                $book = $line['book'];
                $order->items()->create([
                    'book_id' => $book->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                ]);

                $book->stock = $book->stock - $line['quantity'];
                $book->save();
            }

            CartItem::query()->where('cart_id', $cart->id)->delete();

            return $order->load(['items.book.genres']);
        });
    }
}
