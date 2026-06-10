<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Stripe Checkout implementation.
 *
 * NOTE: requires `composer require stripe/stripe-php`. The use statements are
 * left fully-qualified inline so this file still parses if the SDK is absent
 * in a fresh clone — install the SDK before selecting the `stripe` driver.
 */
class StripeGateway implements PaymentGateway
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $webhookSecret,
    ) {
    }

    public function createPayment(Order $order): PaymentSession
    {
        $client = new \Stripe\StripeClient($this->secretKey);

        $session = $client->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => $order->items->map(fn ($item) => [
                'price_data' => [
                    'currency'     => strtolower($order->currency),
                    'product_data' => ['name' => $item->product_name.' ('.$item->sku.')'],
                    'unit_amount'  => (int) round($item->unit_price * 100),
                ],
                'quantity' => $item->quantity,
            ])->all(),
            'success_url' => route('checkout.success', $order).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('checkout.cancel', $order),
            'metadata'    => ['order_id' => (string) $order->id],
        ]);

        return new PaymentSession(redirectUrl: $session->url, reference: $session->id);
    }

    public function verify(Request $request): PaymentResult
    {
        $event = \Stripe\Webhook::constructEvent(
            $request->getContent(),
            $request->header('Stripe-Signature', ''),
            $this->webhookSecret,
        );

        if ($event->type === 'checkout.session.completed') {
            $object = $event->data->object;

            return new PaymentResult(
                success: true,
                orderId: (int) ($object->metadata->order_id ?? 0),
                reference: $object->id,
            );
        }

        return new PaymentResult(success: false, orderId: null);
    }
}
