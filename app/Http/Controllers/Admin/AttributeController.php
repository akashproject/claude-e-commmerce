<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAttributeRequest;
use App\Http\Requests\Admin\StoreAttributeValueRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index(): View
    {
        $attributes = Attribute::with('values')->orderBy('name')->get();

        return view('admin.attributes.index', compact('attributes'));
    }

    public function store(StoreAttributeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);

        Attribute::create($data);

        return back()->with('success', 'Attribute created.');
    }

    public function destroy(Attribute $attribute): RedirectResponse
    {
        // cascade deletes its values (FK cascadeOnDelete) and unlinks variants.
        $attribute->delete();

        return back()->with('success', 'Attribute deleted.');
    }

    public function storeValue(StoreAttributeValueRequest $request, Attribute $attribute): RedirectResponse
    {
        $attribute->values()->create($request->validated());

        return back()->with('success', 'Value added.');
    }

    public function destroyValue(AttributeValue $value): RedirectResponse
    {
        $value->delete();

        return back()->with('success', 'Value removed.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Attribute::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
