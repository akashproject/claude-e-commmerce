<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $product->name }}</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-100 text-red-700">{{ session('error') }}</div>
        @endif

        <div class="bg-white shadow sm:rounded-lg p-6 grid md:grid-cols-2 gap-8"
             x-data="variantSelector(
                 @js($variantPayload),
                 @js($attributes)
             )">

            {{-- Image --}}
            <div>
                <img :src="matched?.image || '{{ $product->base_image ? asset('storage/'.$product->base_image) : 'https://placehold.co/600x600?text=Product' }}'"
                     alt="{{ $product->name }}"
                     class="w-full rounded-lg object-cover">
            </div>

            {{-- Details + selector --}}
            <div>
                <p class="text-gray-600 mb-4">{{ $product->description }}</p>

                {{-- Attribute option groups --}}
                <template x-for="attr in attributes" :key="attr.id">
                    <div class="mb-4">
                        <p class="font-medium text-sm text-gray-700" x-text="attr.name"></p>
                        <div class="flex flex-wrap gap-2 mt-1">
                            <template x-for="val in attr.values" :key="val.id">
                                <button type="button"
                                        @click="select(attr.id, val.id)"
                                        :class="selected[attr.id] === val.id
                                            ? 'ring-2 ring-indigo-600 bg-indigo-50'
                                            : 'border-gray-300'"
                                        class="px-3 py-1 border rounded text-sm"
                                        x-text="val.label"></button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Price + stock --}}
                <div class="my-5">
                    <p class="text-3xl font-bold text-gray-900">
                        <span x-show="matched">&#8377;<span x-text="matched?.price"></span></span>
                        <span x-show="!matched" class="text-base font-normal text-gray-400">
                            Select all options to see price
                        </span>
                    </p>
                    <p class="text-sm mt-1"
                       x-show="matched"
                       :class="inStock ? 'text-green-600' : 'text-red-600'"
                       x-text="inStock ? `In stock (${matched.stock} available)` : 'Out of stock'"></p>
                    <p class="text-xs text-gray-400 mt-1" x-show="matched">SKU: <span x-text="matched?.sku"></span></p>
                </div>

                {{-- Quantity --}}
                <div class="mb-4">
                    <label class="text-sm text-gray-700">Qty</label>
                    <input type="number" min="1" max="99" x-model.number="quantity"
                           class="w-20 border-gray-300 rounded text-sm">
                </div>

                {{-- Actions: both buttons share the resolved variant id --}}
                <div class="flex gap-3">
                    <form action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="variant_id" :value="matched?.id">
                        <input type="hidden" name="quantity" :value="quantity">
                        <button type="submit" :disabled="!canBuy"
                                class="px-5 py-2 bg-gray-800 text-white rounded disabled:opacity-40">
                            Add to Cart
                        </button>
                    </form>

                    <form action="{{ route('checkout.buyNow') }}" method="POST">
                        @csrf
                        <input type="hidden" name="variant_id" :value="matched?.id">
                        <input type="hidden" name="quantity" :value="quantity">
                        <button type="submit" :disabled="!canBuy"
                                class="px-5 py-2 bg-indigo-600 text-white rounded disabled:opacity-40">
                            Buy Now
                        </button>
                    </form>

                    @auth
                        <form action="{{ route('wishlist.toggle', $product) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 border border-gray-300 rounded text-sm">♥ Wishlist</button>
                        </form>
                    @endauth

                    <form action="{{ route('compare.add', $product) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 border border-gray-300 rounded text-sm">⇄ Compare</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function variantSelector(variants, attributes) {
            return {
                variants,            // { "3-7": {id, price, stock, image, sku}, ... }
                attributes,
                selected: {},        // { attributeId: attributeValueId }
                quantity: 1,

                select(attrId, valueId) {
                    this.selected[attrId] = valueId;
                },

                // Must match the server signature: sorted value ids joined by "-".
                get signature() {
                    const ids = Object.values(this.selected);
                    if (ids.length !== this.attributes.length) return null;
                    return ids.map(Number).sort((a, b) => a - b).join('-');
                },

                get matched() {
                    return this.signature ? (this.variants[this.signature] ?? null) : null;
                },

                get inStock() {
                    return this.matched ? this.matched.stock > 0 : false;
                },

                get canBuy() {
                    return this.matched !== null && this.inStock && this.quantity >= 1;
                },
            };
        }
    </script>
</x-app-layout>
