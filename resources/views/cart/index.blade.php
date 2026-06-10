<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Your Cart</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-700">{{ session('success') }}</div>
        @endif

        @if (! $cart || $cart->items->isEmpty())
            <div class="bg-white shadow sm:rounded-lg p-6 text-gray-500">
                Your cart is empty. <a href="{{ route('home') }}" class="text-indigo-600">Continue shopping</a>.
            </div>
        @else
            <div class="bg-white shadow sm:rounded-lg divide-y">
                @foreach ($cart->items as $item)
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium">{{ $item->variant->product->name }}</p>
                            <p class="text-sm text-gray-500">{{ $item->variant->label() }} · {{ $item->variant->sku }}</p>
                            <p class="text-sm text-gray-500">&#8377;{{ number_format((float) $item->unit_price, 2) }} each</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <form action="{{ route('cart.update', $item->variant) }}" method="POST" class="flex items-center gap-1">
                                @csrf @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="0"
                                       class="w-16 border-gray-300 rounded text-sm">
                                <button class="text-sm text-indigo-600">Update</button>
                            </form>
                            <form action="{{ route('cart.remove', $item->variant) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="text-sm text-red-600">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center justify-between">
                <p class="text-lg font-semibold">Subtotal: &#8377;{{ number_format($cart->subtotal(), 2) }}</p>
                <a href="{{ route('checkout.show') }}"
                   class="px-6 py-2 bg-indigo-600 text-white rounded">Proceed to Checkout</a>
            </div>
        @endif
    </div>
</x-app-layout>
