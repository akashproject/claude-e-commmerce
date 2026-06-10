<?php

namespace App\Http\Controllers;

use App\Http\Requests\BuyNowRequest;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\CheckoutSourceResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutSourceResolver $resolver)
    {
    }

    /**
     * FLOW B — Buy Now. Stores a single-item payload in its OWN session key and
     * redirects to checkout in buy_now mode. Never touches the persistent cart.
     */
    public function buyNow(BuyNowRequest $request): RedirectResponse
    {
        $variant = ProductVariant::findOrFail($request->integer('variant_id'));
        $quantity = $request->integer('quantity', 1) ?: 1;

        if (! $variant->inStock($quantity)) {
            return back()->with('error', 'That variant is out of stock.');
        }

        $request->session()->put('buy_now', [
            'variant_id' => $variant->id,
            'quantity'   => $quantity,
        ]);

        return redirect()->route('checkout.show', ['mode' => 'buy_now']);
    }

    /**
     * Checkout page. Resolves whichever source applies and renders the form.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $source = $this->resolver->resolve($request);

        if ($source->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Nothing to check out.');
        }

        return view('checkout.show', [
            'lines'    => $source->lines,
            'subtotal' => $source->subtotal(),
            'mode'     => $request->query('mode', 'cart'),
        ]);
    }

    /**
     * Local-only simulated payment page (FakeGateway).
     */
    public function fake(Order $order): View
    {
        return view('checkout.fake', compact('order'));
    }

    public function success(Order $order): View
    {
        return view('checkout.success', compact('order'));
    }

    public function cancel(Order $order): View
    {
        return view('checkout.cancel', compact('order'));
    }
}
