<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Razorpay implementation using the Orders API + hosted checkout handoff.
 *
 * For production you would typically render the Razorpay checkout.js widget on
 * the success page with the order reference returned here, then verify the
 * signature on callback. This shows the clean service boundary.
 */
class RazorpayGateway implements PaymentGateway
{
    public function __construct(
        private readonly string $keyId,
        private readonly string $keySecret,
    ) {
    }

    public function createPayment(Order $order): PaymentSession
    {
        $response = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount'   => (int) round($order->total * 100), // paise
                'currency' => $order->currency,
                'receipt'  => $order->order_number,
                'notes'    => ['order_id' => (string) $order->id],
            ])
            ->throw()
            ->json();

        // The customer is sent to our own page that boots razorpay checkout.js
        // with this reference; we keep the redirect contract uniform.
        return new PaymentSession(
            redirectUrl: route('checkout.razorpay', ['order' => $order, 'rzp_order' => $response['id']]),
            reference: $response['id'],
        );
    }

    public function verify(Request $request): PaymentResult
    {
        $expected = hash_hmac(
            'sha256',
            $request->input('razorpay_order_id').'|'.$request->input('razorpay_payment_id'),
            $this->keySecret,
        );

        $valid = hash_equals($expected, (string) $request->input('razorpay_signature'));

        return new PaymentResult(
            success: $valid,
            orderId: $valid ? (int) $request->input('order_id') : null,
            reference: $request->input('razorpay_payment_id'),
        );
    }
}
