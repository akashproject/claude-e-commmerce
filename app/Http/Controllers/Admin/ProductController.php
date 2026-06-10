<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Attribute;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->withCount('variants')
            ->latest()
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['base_image'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);

        $product = Product::create($data);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Product created. Now generate its variants below.');
    }

    public function edit(Product $product): View
    {
        $product->load('variants.attributeValues.attribute');

        // All attributes (with values) available to build variants from.
        $attributes = Attribute::with('values')->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'attributes'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        // Re-slug only if the name changed.
        if ($data['name'] !== $product->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        }

        if ($request->hasFile('image')) {
            if ($product->base_image) {
                Storage::disk('public')->delete($product->base_image);
            }
            $data['base_image'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);

        $product->update($data);

        return back()->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->base_image) {
            Storage::disk('public')->delete($product->base_image);
        }

        $product->delete(); // soft delete; variants remain for order history integrity

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Product::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
