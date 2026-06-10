<?php

namespace App\Services\Payments;

/**
 * The gateway's response after initiating a payment — typically a hosted
 * checkout URL the customer is redirected to.
 */
final class PaymentSession
{
    public function __construct(
        public readonly string $redirectUrl,
        public readonly string $reference,
    ) {
    }
}
