<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use YooKassa\Client as YooKassaClient;

class PaymentService
{
    public function client(): YooKassaClient
    {
        $client = new YooKassaClient();
        $client->setAuth(config('services.yookassa.shop_id'), config('services.yookassa.api_key'));
        return $client;
    }

    /**
     * Create a payment in Yookassa for the given order.
     */
    public function createPayment(Order $order, string $returnUrl): Payment
    {
        // Refresh order with full relationships from database
        $order = $order->fresh(['items.book.genres', 'items.book.author', 'user']);

        // Ensure we have user email
        $email = $order->user?->email ?? auth()->user()?->email;

        if (!$email) {
            throw new \Exception('Невозможно создать платёж: отсутствует email пользователя', 400);
        }

        // Build receipt items for Yookassa
        $receiptItems = [];
        foreach ($order->items as $item) {
            $receiptItems[] = [
                'description' => $item->book->title,
                'quantity' => $item->quantity,
                'amount' => [
                    'value' => number_format($item->unit_price, 2, '.', ''),
                    'currency' => 'RUB',
                ],
                'vat_code' => 1, // VAT 0% (no VAT)
            ];
        }

        $receipt = [
            'customer' => [
                'email' => (string) $email,
            ],
            'items' => $receiptItems,
            'tax_system_code' => 1, // General tax system
        ];

        $payment = $this->client()->createPayment([
            'amount' => [
                'value' => number_format($order->total, 2, '.', ''),
                'currency' => 'RUB',
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => $returnUrl,
            ],
            'capture' => true,
            'description' => "Order #{$order->id} in BookShop",
            'receipt' => $receipt,
        ], uniqid('', true));

        return Payment::create([
            'order_id' => $order->id,
            'yookassa_payment_id' => $payment->getId(),
            'amount' => $order->total,
            'currency' => 'RUB',
            'status' => $payment->getStatus(),
            'payment_url' => $payment->getConfirmation()->getConfirmationUrl(),
        ]);
    }

    /**
     * Process webhook notification from Yookassa.
     */
    public function processWebhook(Request $request): Payment
    {
        \Log::info('Webhook received from Yookassa', [
            'payload' => $request->json()->all(),
        ]);

        $payload = $request->json()->all();

        // Verify we have a valid payload
        if (!isset($payload['object']['id']) || !isset($payload['object']['status'])) {
            throw new \InvalidArgumentException('Invalid webhook payload');
        }

        $paymentId = $payload['object']['id'];
        $status = $payload['object']['status'];

        \Log::info('Processing webhook for payment', [
            'payment_id' => $paymentId,
            'status' => $status,
        ]);

        // Find payment in database - this ensures webhook is legitimate
        $payment = Payment::where('yookassa_payment_id', $paymentId)->firstOrFail();

        $payment->update([
            'status' => $status,
            'paid_at' => $status === 'succeeded' ? now() : $payment->paid_at,
        ]);

        // Update order status if payment succeeded
        if ($status === 'succeeded') {
            $payment->order->update(['status' => OrderStatus::Paid]);
            \Log::info('Order status updated to Paid', [
                'order_id' => $payment->order_id,
                'payment_id' => $paymentId,
            ]);
        }

        return $payment;
    }

    /**
     * Get payment status from Yookassa.
     */
    public function getPaymentStatus(string $paymentId): array
    {
        $payment = $this->client()->getPaymentInfo($paymentId);

        return [
            'id' => $payment->getId(),
            'status' => $payment->getStatus(),
            'amount' => $payment->getAmount()->getValue(),
            'currency' => $payment->getAmount()->getCurrency(),
        ];
    }
}
