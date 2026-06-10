<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Gateway-agnostic contract. Swap Stripe/Razorpay/Fake through a single
 * container binding without touching checkout logic.
 */
interface PaymentGateway
{
    /**
     * Initiate a payment for the given order and return where to send the user.
     */
    public function createPayment(Order $order): PaymentSession;

    /**
     * Verify an incoming webhook / return callback and report the outcome.
     */
    public function verify(Request $request): PaymentResult;
}
