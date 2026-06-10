<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OrderService
{
    /**
     * Create a pending order from a normalised checkout source, decrementing
     * stock atomically under row locks to prevent overselling. Both the cart
     * flow and the buy-now flow funnel through here.
     *
     * @param  array<string, mixed>  $shipping
     * @param  array<string, mixed>|null  $billing
     */
    public function placePending(
        CheckoutSource $source,
        ?int $userId,
        array $shipping,
        ?array $billing = null,
        string $currency = 'INR',
    ): Order {
        if ($source->isEmpty()) {
            throw new RuntimeException('Cannot place an order with no items.');
        }

        return DB::transaction(function () use ($source, $userId, $shipping, $billing, $currency) {
            $subtotal = 0.0;
            $locked = [];

            // Re-read & lock every variant; validate stock inside the transaction.
            foreach ($source->lines as $line) {
                /** @var ProductVariant $variant */
                $variant = ProductVariant::with('product', 'attributeValues.attribute')
                    ->lockForUpdate()
                    ->findOrFail($line['variant']->id);

                if (! $variant->inStock($line['quantity'])) {
                    throw new RuntimeException("Insufficient stock for {$variant->sku}.");
                }

                $locked[] = ['variant' => $variant, 'quantity' => $line['quantity']];
                $subtotal += $variant->price * $line['quantity'];
            }

            $order = Order::create([
                'user_id'          => $userId,
                'order_number'     => 'ORD-'.strtoupper(Str::random(10)),
                'status'           => 'pending',
                'subtotal'         => $subtotal,
                'shipping'         => 0,
                'total'            => $subtotal,
                'currency'         => $currency,
                'payment_status'   => 'unpaid',
                'shipping_address' => $shipping,
                'billing_address'  => $billing,
            ]);

            foreach ($locked as $line) {
                /** @var ProductVariant $variant */
                $variant = $line['variant'];

                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'product_name'       => $variant->product->name,
                    'sku'                => $variant->sku,
                    'variant_attributes' => $variant->attributeValues
                        ->mapWithKeys(fn ($av) => [$av->attribute->name => $av->value])
                        ->all(),
                    'unit_price'         => $variant->price,
                    'quantity'           => $line['quantity'],
                    'line_total'         => $variant->price * $line['quantity'],
                ]);

                $variant->decrement('stock', $line['quantity']);
            }

            return $order;
        });
    }

    /**
     * Mark an order paid (called after gateway confirms success).
     */
    public function markPaid(Order $order, ?string $reference = null): void
    {
        $order->update([
            'payment_status'    => 'paid',
            'status'            => 'paid',
            'payment_reference' => $reference ?? $order->payment_reference,
        ]);
    }

    /**
     * Payment failed/abandoned: restore stock and cancel.
     */
    public function markFailed(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    ProductVariant::whereKey($item->product_variant_id)
                        ->lockForUpdate()
                        ->increment('stock', $item->quantity);
                }
            }

            $order->update(['payment_status' => 'failed', 'status' => 'cancelled']);
        });
    }
}
