<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutSourceResolver;
use App\Services\OrderService;
use App\Services\Payments\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly OrderService $orders,
        private readonly CartService $cart,
        private readonly CheckoutSourceResolver $resolver,
    ) {
    }

    /**
     * Shared placement endpoint for BOTH flows. `mode` selects the source; from
     * there the pipeline (stock lock, order creation, payment) is identical.
     */
    public function place(PlaceOrderRequest $request): RedirectResponse
    {
        $mode = $request->input('mode', 'cart');

        $source = $this->resolver->resolve($request);

        if ($source->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Nothing to check out.');
        }

        $billing = $request->boolean('billing_same', true)
            ? $request->validated('shipping')
            : $request->validated('billing');

        $order = $this->orders->placePending(
            source: $source,
            userId: $request->user()?->id,
            shipping: $request->validated('shipping'),
            billing: $billing,
        );

        // Initiate payment; persist gateway + reference on the order.
        $session = $this->gateway->createPayment($order);
        $order->update([
            'payment_gateway'   => config('services.payment_driver'),
            'payment_reference' => $session->reference,
        ]);

        // Clear only the source that was actually consumed.
        $mode === 'buy_now'
            ? $request->session()->forget('buy_now')
            : $this->cart->clear();

        return redirect()->away($session->redirectUrl);
    }

    /**
     * Stripe webhook / Razorpay callback endpoint. Verifies then settles.
     */
    public function paymentCallback(Request $request): RedirectResponse
    {
        $result = $this->gateway->verify($request);

        $order = $result->orderId ? Order::find($result->orderId) : null;

        if ($order && $result->success) {
            $this->orders->markPaid($order, $result->reference);

            return redirect()->route('checkout.success', $order);
        }

        if ($order) {
            $this->orders->markFailed($order);

            return redirect()->route('checkout.cancel', $order);
        }

        return redirect()->route('home');
    }

    /**
     * FakeGateway decision handler (local dev only).
     */
    public function fakeDecision(Request $request, Order $order): RedirectResponse
    {
        if ($request->input('decision') === 'success') {
            $this->orders->markPaid($order);

            return redirect()->route('checkout.success', $order);
        }

        $this->orders->markFailed($order);

        return redirect()->route('checkout.cancel', $order);
    }
}
