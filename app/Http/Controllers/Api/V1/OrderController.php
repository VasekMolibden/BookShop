<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckoutRequest;
use App\Http\Requests\Api\V1\OrderIndexRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService
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
     * Place an order from the current cart and create payment.
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $order = $this->orderService->checkoutFromCart($request->user());

        // Create payment in Yookassa
        $returnUrl = url('/orders/' . $order->id);
        $payment = $this->paymentService->createPayment($order, $returnUrl);

        return ApiResponse::success([
            'order' => new OrderResource($order),
            'payment' => [
                'id' => $payment->id,
                'yookassa_payment_id' => $payment->yookassa_payment_id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'payment_url' => $payment->payment_url,
            ],
        ], 'Заказ создан, перейдите к оплате', 201);
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

    /**
     * Update order status (user can only cancel pending orders).
     */
    public function update(Order $order, Request $request): JsonResponse
    {
        // Verify user owns this order
        if ((int) $request->user()?->id !== (int) $order->user_id) {
            abort(404);
        }

        $status = $request->input('status');

        // Check if order can be cancelled (only pending)
        if ($status !== 'cancelled' || $order->status !== OrderStatus::from('pending')) {
            return ApiResponse::error(400, 'Можно отменить только неоплаченные заказы', null, 400);
        }

        $order->update(['status' => OrderStatus::from('cancelled')]);

        return ApiResponse::success([
            'order' => new OrderResource($order),
        ], 'Заказ отменён');
    }
}
