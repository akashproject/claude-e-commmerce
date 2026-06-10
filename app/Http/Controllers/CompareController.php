<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Session-backed product comparison, capped at 4 products. Builds a unified
 * attribute matrix so the view can render rows of attribute -> value per product.
 */
class CompareController extends Controller
{
    private const MAX = 4;
    private const KEY = 'compare';

    public function index(Request $request): View
    {
        $ids = $request->session()->get(self::KEY, []);

        $products = Product::query()
            ->whereIn('id', $ids)
            ->with('variants.attributeValues.attribute')
            ->get();

        // Union of every attribute name across the compared products.
        $attributeNames = $products
            ->flatMap(fn (Product $p) => $p->usedAttributes()->pluck('name'))
            ->unique()
            ->values();

        // matrix[productId][attributeName] = "Red, Blue"
        $matrix = $products->mapWithKeys(function (Product $p) {
            $byAttr = $p->usedAttributes()->mapWithKeys(fn ($attr) => [
                $attr->name => $attr->values->pluck('value')->join(', '),
            ]);

            return [$p->id => $byAttr];
        });

        return view('compare.index', compact('products', 'attributeNames', 'matrix'));
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $ids = collect($request->session()->get(self::KEY, []));

        if ($ids->contains($product->id)) {
            return back()->with('error', 'Already in comparison.');
        }

        if ($ids->count() >= self::MAX) {
            return back()->with('error', 'You can compare up to '.self::MAX.' products.');
        }

        $request->session()->put(self::KEY, $ids->push($product->id)->all());

        return back()->with('success', 'Added to comparison.');
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $ids = collect($request->session()->get(self::KEY, []))
            ->reject(fn ($id) => $id === $product->id)
            ->values()
            ->all();

        $request->session()->put(self::KEY, $ids);

        return back()->with('success', 'Removed from comparison.');
    }
}
