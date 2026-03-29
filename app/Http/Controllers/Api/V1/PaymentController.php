<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    /**
     * Handle webhook notification from Yookassa.
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $this->paymentService->processWebhook($request);
            return ApiResponse::success(null, 'Webhook received', 200);
        } catch (\Throwable $e) {
            return ApiResponse::error(400, $e->getMessage(), null, 400);
        }
    }
}
