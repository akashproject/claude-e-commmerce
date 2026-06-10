<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $products = $request->user()->wishlist()
            ->with(['variants' => fn ($q) => $q->where('is_active', true)])
            ->paginate(12);

        return view('wishlist.index', compact('products'));
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $result = $request->user()->wishlist()->toggle($product->id);

        $added = in_array($product->id, $result['attached'], true);

        return back()->with('success', $added ? 'Added to wishlist.' : 'Removed from wishlist.');
    }
}
