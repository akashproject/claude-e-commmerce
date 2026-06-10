<?php

namespace App\Services;

use App\Models\ProductVariant;

/**
 * A normalised collection of checkout line items. Both the standard cart flow
 * and the "Buy Now" flow produce one of these, so the order-placement and
 * payment pipeline downstream is identical regardless of source.
 */
final class CheckoutSource
{
    /**
     * @param  array<int, array{variant: ProductVariant, quantity: int}>  $lines
     */
    public function __construct(public readonly array $lines)
    {
    }

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }

    public function subtotal(): float
    {
        return array_reduce(
            $this->lines,
            fn (float $carry, array $line) => $carry + ($line['variant']->price * $line['quantity']),
            0.0
        );
    }
}
