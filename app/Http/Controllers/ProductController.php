<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with(['variants' => fn($q) => $q->where('is_active', true)])
            ->paginate(12);

        return view('products.index', compact('products'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'variants' => fn($q) => $q->where('is_active', true),
            'variants.attributeValues.attribute',
        ]);

        // signature => variant payload, consumed by the Alpine selector.
        $variantPayload = $product->variants->mapWithKeys(function ($variant) {
            $signature = $variant->attributeValues
                ->pluck('id')->sort()->values()->implode('-');

            return [
                $signature => [
                    'id' => $variant->id,
                    'price' => number_format((float) $variant->price, 2, '.', ''),
                    'stock' => $variant->stock,
                    'image' => $variant->image ? asset('storage/' . $variant->image) : null,
                    'sku' => $variant->sku,
                ]
            ];
        });

        $attributes = $product->usedAttributes()->map(fn($attr) => [
            'id' => $attr->id,
            'name' => $attr->name,
            'type' => $attr->type,
            'values' => $attr->values->map(fn($v) => [
                'id' => $v->id,
                'label' => $v->value,
                'swatch' => $v->swatch,
            ])->values(),
        ]);

        // dd($product, $variantPayload, $attributes);

        return view('products.show', compact('product', 'variantPayload', 'attributes'));
    }
}
