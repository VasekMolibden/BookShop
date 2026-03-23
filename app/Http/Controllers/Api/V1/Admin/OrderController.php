<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\AdminOrderListRequest;
use App\Http\Requests\Api\V1\Admin\UpdateAdminOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Services\Admin\AdminOrderService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly AdminOrderService $orderService
    ) {}

    /**
     * Paginated list of all orders (admin).
     */
    public function index(AdminOrderListRequest $request): JsonResponse
    {
        $perPage = (int) ($request->validated('per_page') ?? 15);
        $paginator = $this->orderService->paginateAll($perPage);

        return ApiResponse::success([
            'orders' => OrderResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 'Заказы (админ)');
    }

    /**
     * Update order status.
     */
    public function update(UpdateAdminOrderRequest $request, Order $order): JsonResponse
    {
        $raw = $request->validated('status');
        $status = $raw instanceof OrderStatus ? $raw : OrderStatus::from((string) $raw);

        $order = $this->orderService->updateStatus($order, $status);

        return ApiResponse::success([
            'order' => new OrderResource($order),
        ], 'Статус заказа обновлён');
    }
}
