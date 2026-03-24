<?php

namespace App\Services\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminOrderService
{
    /**
     * Paginated list of all orders (newest first) for the admin panel.
     */
    public function paginateAll(int $perPage = 15): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 100);

        return Order::query()
            ->with(['user:id,name,email', 'items.book:id,title,author,author_id', 'items.book.author:id,name,slug', 'items.book.genres:id,name,slug'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Update order status (e.g. paid, shipped, cancelled).
     */
    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        $order->status = $status;
        $order->save();

        return $order->fresh(['items.book.genres', 'items.book.author']);
    }
}
