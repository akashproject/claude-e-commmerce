<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Database-backed cart. Resolves (and lazily creates) the active cart for the
 * current user, falling back to a session-keyed guest cart. On login the guest
 * cart can be merged into the user's cart via merge().
 */
class CartService
{
    public function __construct(private readonly Request $request)
    {
    }

    public function current(bool $create = true): ?Cart
    {
        $query = Auth::check()
            ? Cart::query()->where('user_id', Auth::id())
            : Cart::query()->where('session_id', $this->request->session()->getId());

        $cart = $query->with('items.variant.product')->first();

        if (! $cart && $create) {
            $cart = Cart::create(Auth::check()
                ? ['user_id' => Auth::id()]
                : ['session_id' => $this->request->session()->getId()]);
        }

        return $cart;
    }

    public function add(ProductVariant $variant, int $quantity = 1): void
    {
        $cart = $this->current();

        $item = $cart->items()->firstOrNew(['product_variant_id' => $variant->id]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $item->unit_price = $variant->price;
        $item->save();
    }

    public function updateQuantity(ProductVariant $variant, int $quantity): void
    {
        $cart = $this->current();

        if ($quantity <= 0) {
            $cart->items()->where('product_variant_id', $variant->id)->delete();

            return;
        }

        $cart->items()->updateOrCreate(
            ['product_variant_id' => $variant->id],
            ['quantity' => $quantity, 'unit_price' => $variant->price],
        );
    }

    public function remove(ProductVariant $variant): void
    {
        $this->current()?->items()->where('product_variant_id', $variant->id)->delete();
    }

    /**
     * Normalise the cart into the shape the checkout pipeline consumes.
     *
     * @return array<int, array{variant: ProductVariant, quantity: int}>
     */
    public function lines(): array
    {
        $cart = $this->current(create: false);

        if (! $cart) {
            return [];
        }

        return $cart->items
            ->filter(fn ($item) => $item->variant !== null)
            ->map(fn ($item) => ['variant' => $item->variant, 'quantity' => $item->quantity])
            ->values()
            ->all();
    }

    public function clear(): void
    {
        $this->current(create: false)?->items()->delete();
    }

    /**
     * Merge the guest (session) cart into the authenticated user's cart.
     * Call this from a login listener.
     */
    public function mergeGuestCartInto(int $userId): void
    {
        $guest = Cart::where('session_id', $this->request->session()->getId())
            ->with('items')->first();

        if (! $guest) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $userId]);

        foreach ($guest->items as $item) {
            $merged = $userCart->items()->firstOrNew(['product_variant_id' => $item->product_variant_id]);
            $merged->quantity = ($merged->exists ? $merged->quantity : 0) + $item->quantity;
            $merged->unit_price = $item->unit_price;
            $merged->save();
        }

        $guest->delete();
    }
}
