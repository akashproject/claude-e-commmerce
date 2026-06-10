<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Local / testing gateway. Skips any real network call and routes the customer
 * straight to a simulated payment page where they can mark success or failure.
 * Selected by default so the app is runnable with no API keys.
 */
class FakeGateway implements PaymentGateway
{
    public function createPayment(Order $order): PaymentSession
    {
        $reference = 'FAKE-'.Str::upper(Str::random(12));

        return new PaymentSession(
            redirectUrl: route('checkout.fake', ['order' => $order]),
            reference: $reference,
        );
    }

    public function verify(Request $request): PaymentResult
    {
        $approved = $request->input('decision') === 'success';

        return new PaymentResult(
            success: $approved,
            orderId: (int) $request->input('order_id'),
            reference: 'FAKE-'.Str::upper(Str::random(12)),
        );
    }
}
