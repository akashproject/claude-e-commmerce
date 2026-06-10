<?php

namespace App\Services\Payments;

/**
 * Outcome of verifying a gateway callback / webhook.
 */
final class PaymentResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?int $orderId,
        public readonly ?string $reference = null,
    ) {
    }
}
