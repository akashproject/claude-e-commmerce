<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Http\Request;

/**
 * Resolves the line items for checkout from whichever source the request
 * indicates. This is the single branch point between the "Buy Now" and
 * standard cart flows — everything downstream consumes the same shape.
 */
class CheckoutSourceResolver
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function resolve(Request $request): CheckoutSource
    {
        if ($request->input('mode') === 'buy_now' && $request->session()->has('buy_now')) {
            $payload = $request->session()->get('buy_now');
            $variant = ProductVariant::with('product')->find($payload['variant_id']);

            return new CheckoutSource(
                $variant ? [['variant' => $variant, 'quantity' => (int) $payload['quantity']]] : []
            );
        }

        return new CheckoutSource($this->cart->lines());
    }
}
