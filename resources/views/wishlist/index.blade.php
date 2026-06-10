<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Wishlist</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-700">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @forelse ($products as $product)
                @php $from = $product->variants->min('price'); @endphp
                <div class="bg-white shadow rounded-lg p-4">
                    <a href="{{ route('products.show', $product) }}">
                        <img src="{{ $product->base_image ? asset('storage/'.$product->base_image) : 'https://placehold.co/400x400?text=Product' }}"
                             class="w-full aspect-square object-cover rounded mb-3" alt="{{ $product->name }}">
                        <h3 class="font-medium">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-500">@if ($from) From &#8377;{{ number_format((float) $from, 2) }} @endif</p>
                    </a>
                    <form action="{{ route('wishlist.toggle', $product) }}" method="POST" class="mt-2">
                        @csrf
                        <button class="text-sm text-red-600">Remove</button>
                    </form>
                </div>
            @empty
                <p class="text-gray-500 col-span-full">Your wishlist is empty.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $products->links() }}</div>
    </div>
</x-app-layout>
