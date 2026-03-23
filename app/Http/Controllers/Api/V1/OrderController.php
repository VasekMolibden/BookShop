<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckoutRequest;
use App\Http\Requests\Api\V1\OrderIndexRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * List the authenticated user's orders.
     */
    public function index(OrderIndexRequest $request): JsonResponse
    {
        $perPage = (int) ($request->validated('per_page') ?? 15);
        $paginator = $this->orderService->paginateForUser($request->user(), $perPage);

        return ApiResponse::success([
            'orders' => OrderResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 'Список заказов');
    }

    /**
     * Place an order from the current cart (stock is decreased, cart cleared).
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $order = $this->orderService->checkoutFromCart($request->user());

        return ApiResponse::success([
            'order' => new OrderResource($order),
        ], 'Заказ оформлен', 201);
    }

    /**
     * Show a single order belonging to the authenticated user.
     */
    public function show(Order $order): JsonResponse
    {
        return ApiResponse::success([
            'order' => new OrderResource($order),
        ], 'Заказ');
    }
}
