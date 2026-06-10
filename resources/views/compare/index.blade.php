<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Compare Products</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-100 text-red-700">{{ session('error') }}</div>
        @endif

        @if ($products->isEmpty())
            <div class="bg-white shadow sm:rounded-lg p-6 text-gray-500">
                No products to compare. Add up to 4 from any product page.
            </div>
        @else
            <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="p-3 text-left text-gray-500">Attribute</th>
                            @foreach ($products as $product)
                                <th class="p-3 text-left">
                                    <a href="{{ route('products.show', $product) }}" class="font-medium text-indigo-600">{{ $product->name }}</a>
                                    <form action="{{ route('compare.remove', $product) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-500">remove</button>
                                    </form>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b">
                            <td class="p-3 text-gray-500">Price from</td>
                            @foreach ($products as $product)
                                <td class="p-3">&#8377;{{ number_format((float) $product->variants->min('price'), 2) }}</td>
                            @endforeach
                        </tr>
                        @foreach ($attributeNames as $name)
                            <tr class="border-b">
                                <td class="p-3 text-gray-500">{{ $name }}</td>
                                @foreach ($products as $product)
                                    <td class="p-3">{{ $matrix[$product->id][$name] ?? '—' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
