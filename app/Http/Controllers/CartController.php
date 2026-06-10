<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(): View
    {
        return view('cart.index', [
            'cart' => $this->cart->current(create: false),
        ]);
    }

    public function add(AddToCartRequest $request): RedirectResponse
    {
        $variant = ProductVariant::findOrFail($request->integer('variant_id'));
        $quantity = $request->integer('quantity', 1) ?: 1;

        if (! $variant->inStock($quantity)) {
            return back()->with('error', 'That variant is out of stock.');
        }

        $this->cart->add($variant, $quantity);

        return redirect()->route('cart.index')->with('success', 'Added to cart.');
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $this->cart->updateQuantity($variant, (int) $request->input('quantity', 1));

        return back()->with('success', 'Cart updated.');
    }

    public function remove(ProductVariant $variant): RedirectResponse
    {
        $this->cart->remove($variant);

        return back()->with('success', 'Item removed.');
    }
}
