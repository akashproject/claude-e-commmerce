<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVariantsRequest;
use App\Http\Requests\Admin\SuggestVariantsRequest;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\VariantGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductVariantController extends Controller
{
    public function __construct(private readonly VariantGenerator $generator)
    {
    }

    /**
     * Step 1 — preview all combinations (Cartesian product) for the selected
     * attribute values. No DB writes; returns JSON the admin UI renders as an
     * editable grid (price / stock / sku per row).
     */
    public function suggest(SuggestVariantsRequest $request, Product $product): JsonResponse
    {
        // Group selected values by their attribute, preserving Eloquent models.
        $groups = AttributeValue::with('attribute')
            ->whereIn('id', $request->validated('attribute_value_ids'))
            ->get()
            ->groupBy('attribute_id')
            ->map(fn ($collection) => $collection->values()->all())
            ->values()
            ->all();

        $combinations = collect($this->generator->cartesian($groups))
            ->map(fn (array $combo) => [
                'label'               => collect($combo)->pluck('value')->join(' / '),
                'attribute_value_ids' => collect($combo)->pluck('id')->values(),
                'suggested_sku'       => $this->buildSku($product, $combo),
                'exists'              => $this->combinationExists($product, collect($combo)->pluck('id')->all()),
            ]);

        return response()->json(['combinations' => $combinations]);
    }

    /**
     * Step 2 — persist the (possibly edited) combinations the admin confirmed.
     */
    public function store(StoreVariantsRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product) {
            foreach ($request->validated('variants') as $row) {
                if ($this->combinationExists($product, $row['attribute_value_ids'])) {
                    continue; // skip duplicates
                }

                $variant = $product->variants()->create([
                    'sku'   => $row['sku'],
                    'price' => $row['price'],
                    'stock' => $row['stock'] ?? 0,
                    'image' => $row['image'] ?? null,
                ]);

                $variant->attributeValues()->sync($row['attribute_value_ids']);
            }
        });

        return back()->with('success', 'Variants generated.');
    }

    /**
     * Inline edit of an existing variant's price / stock / status.
     */
    public function update(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($variant->product_id === $product->id, 404);

        $validated = $request->validate([
            'price'     => ['required', 'numeric', 'min:0'],
            'stock'     => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $variant->update([
            'price'     => $validated['price'],
            'stock'     => $validated['stock'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', "Variant {$variant->sku} updated.");
    }

    public function destroy(Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($variant->product_id === $product->id, 404);

        $variant->delete();

        return back()->with('success', 'Variant removed.');
    }

    /**
     * @param  array<int, AttributeValue>  $combo
     */
    private function buildSku(Product $product, array $combo): string
    {
        $suffix = collect($combo)
            ->map(fn (AttributeValue $v) => Str::upper(Str::substr(Str::slug($v->value), 0, 3)))
            ->join('-');

        return Str::upper(Str::slug($product->name)).'-'.$suffix;
    }

    /**
     * @param  array<int, int>  $valueIds
     */
    private function combinationExists(Product $product, array $valueIds): bool
    {
        sort($valueIds);

        return $product->variants()
            ->with('attributeValues:id')
            ->get()
            ->contains(function ($variant) use ($valueIds) {
                $existing = $variant->attributeValues->pluck('id')->sort()->values()->all();

                return $existing === $valueIds;
            });
    }
}
