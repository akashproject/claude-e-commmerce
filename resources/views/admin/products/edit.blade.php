<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin · Edit “{{ $product->name }}”</h2>
            <a href="{{ route('admin.products.index') }}" class="text-sm text-indigo-600">← All products</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="p-3 rounded bg-green-100 text-green-700">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="p-3 rounded bg-red-100 text-red-700 text-sm">
                <ul class="list-disc ml-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- ============ Product details ============ --}}
        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data"
              class="bg-white shadow sm:rounded-lg p-6 space-y-4">
            @csrf @method('PATCH')
            <h3 class="font-semibold">Product details</h3>

            <div class="grid md:grid-cols-3 gap-4">
                <div class="md:col-span-2 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input name="name" value="{{ old('name', $product->name) }}" required class="mt-1 w-full border-gray-300 rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="4" class="mt-1 w-full border-gray-300 rounded">{{ old('description', $product->description) }}</textarea>
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked($product->is_active)> Active
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Base image</label>
                    <img src="{{ $product->base_image ? asset('storage/'.$product->base_image) : 'https://placehold.co/200x200?text=No+image' }}"
                         class="mt-1 w-full aspect-square object-cover rounded border">
                    <input type="file" name="image" accept="image/*" class="mt-2 w-full text-xs">
                </div>
            </div>
            <button class="px-5 py-2 bg-indigo-600 text-white rounded">Save details</button>
        </form>

        {{-- ============ Existing variants ============ --}}
        <div class="bg-white shadow sm:rounded-lg p-6">
            <h3 class="font-semibold mb-3">Variants ({{ $product->variants->count() }})</h3>
            @if ($product->variants->isEmpty())
                <p class="text-gray-500 text-sm">No variants yet — generate some below.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-2 text-left">SKU</th>
                                <th class="p-2 text-left">Attributes</th>
                                <th class="p-2 text-left">Price</th>
                                <th class="p-2 text-left">Stock</th>
                                <th class="p-2 text-left">Active</th>
                                <th class="p-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($product->variants as $variant)
                                <tr>
                                    <td class="p-2 font-mono text-xs">{{ $variant->sku }}</td>
                                    <td class="p-2 text-gray-500">{{ $variant->label() }}</td>
                                    <td colspan="3" class="p-2">
                                        <form action="{{ route('admin.products.variants.update', [$product, $variant]) }}"
                                              method="POST" class="flex items-center gap-2">
                                            @csrf @method('PATCH')
                                            <input type="number" step="0.01" name="price" value="{{ $variant->price }}" class="w-28 border-gray-300 rounded text-sm">
                                            <input type="number" name="stock" value="{{ $variant->stock }}" class="w-20 border-gray-300 rounded text-sm">
                                            <label class="flex items-center gap-1 text-xs">
                                                <input type="checkbox" name="is_active" value="1" @checked($variant->is_active)> active
                                            </label>
                                            <button class="text-indigo-600 text-xs">Save</button>
                                        </form>
                                    </td>
                                    <td class="p-2 text-right">
                                        <form action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}"
                                              method="POST" onsubmit="return confirm('Delete this variant?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 text-xs">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ============ Variant generator (Cartesian) ============ --}}
        <div class="bg-white shadow sm:rounded-lg p-6"
             x-data="variantGenerator(
                 @js($attributes->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'values' => $a->values->map(fn($v) => ['id' => $v->id, 'value' => $v->value])])),
                 '{{ route('admin.products.variants.suggest', $product) }}'
             )">
            <h3 class="font-semibold mb-1">Generate variants</h3>
            <p class="text-sm text-gray-500 mb-4">
                Pick the attribute values to combine. We’ll build the Cartesian product
                (e.g. 2 colors × 2 sizes = 4 SKUs). Existing combinations are skipped.
                @if ($attributes->isEmpty())
                    <span class="text-red-600">No attributes defined —
                        <a href="{{ route('admin.attributes.index') }}" class="underline">create some first</a>.</span>
                @endif
            </p>

            {{-- Attribute value pickers --}}
            <div class="space-y-3">
                <template x-for="attr in attributes" :key="attr.id">
                    <div>
                        <p class="text-sm font-medium text-gray-700" x-text="attr.name"></p>
                        <div class="flex flex-wrap gap-2 mt-1">
                            <template x-for="val in attr.values" :key="val.id">
                                <label class="inline-flex items-center gap-1 px-2 py-1 border rounded text-sm cursor-pointer"
                                       :class="selected.includes(val.id) ? 'bg-indigo-50 ring-1 ring-indigo-500' : 'border-gray-300'">
                                    <input type="checkbox" :value="val.id" x-model.number="selected" class="hidden">
                                    <span x-text="val.value"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <button type="button" @click="generate()"
                    :disabled="selected.length === 0 || loading"
                    class="mt-4 px-4 py-2 bg-gray-800 text-white rounded text-sm disabled:opacity-40">
                <span x-show="!loading">Generate combinations</span>
                <span x-show="loading">Generating…</span>
            </button>

            {{-- Suggested combinations grid -> POST to store --}}
            <form :action="storeReady ? '{{ route('admin.products.variants.store', $product) }}' : ''"
                  method="POST" x-show="rows.length" x-cloak class="mt-5">
                @csrf
                <table class="min-w-full text-sm border-t">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="p-2">Combination</th>
                            <th class="p-2">SKU</th>
                            <th class="p-2">Price</th>
                            <th class="p-2">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, i) in rows" :key="row.key">
                            <tr :class="row.exists ? 'opacity-40' : ''">
                                <td class="p-2" x-text="row.label"></td>
                                <td class="p-2">
                                    <input :name="`variants[${i}][sku]`" x-model="row.sku" :disabled="row.exists"
                                           class="w-44 border-gray-300 rounded text-xs font-mono">
                                    <template x-for="vid in row.attribute_value_ids">
                                        <input type="hidden" :name="`variants[${i}][attribute_value_ids][]`" :value="vid" :disabled="row.exists">
                                    </template>
                                </td>
                                <td class="p-2">
                                    <input type="number" step="0.01" :name="`variants[${i}][price]`" x-model.number="row.price" :disabled="row.exists"
                                           class="w-28 border-gray-300 rounded text-xs">
                                </td>
                                <td class="p-2">
                                    <input type="number" :name="`variants[${i}][stock]`" x-model.number="row.stock" :disabled="row.exists"
                                           class="w-20 border-gray-300 rounded text-xs">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <button type="submit" class="mt-3 px-5 py-2 bg-indigo-600 text-white rounded text-sm">Save new variants</button>
                <span class="text-xs text-gray-400 ml-2">Greyed-out rows already exist and won’t be re-created.</span>
            </form>
        </div>
    </div>

    <script>
        function variantGenerator(attributes, suggestUrl) {
            return {
                attributes,
                suggestUrl,
                selected: [],     // chosen attribute_value ids
                rows: [],
                loading: false,
                get storeReady() { return this.rows.some(r => !r.exists); },

                async generate() {
                    this.loading = true;
                    try {
                        const res = await fetch(this.suggestUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ attribute_value_ids: this.selected }),
                        });
                        const data = await res.json();
                        this.rows = (data.combinations || []).map(c => ({
                            key: c.attribute_value_ids.join('-'),
                            label: c.label,
                            sku: c.suggested_sku,
                            attribute_value_ids: c.attribute_value_ids,
                            price: 0,
                            stock: 0,
                            exists: c.exists,
                        }));
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>

    <style>[x-cloak]{display:none!important}</style>
</x-app-layout>
