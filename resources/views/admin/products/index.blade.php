<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin · Products</h2>
            <a href="{{ route('admin.products.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">+ New Product</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-700">{{ session('success') }}</div>
        @endif

        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">Product</th>
                        <th class="p-3 text-left">Slug</th>
                        <th class="p-3 text-left">Variants</th>
                        <th class="p-3 text-left">Active</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($products as $product)
                        <tr>
                            <td class="p-3 font-medium flex items-center gap-3">
                                <img src="{{ $product->base_image ? asset('storage/'.$product->base_image) : 'https://placehold.co/48x48?text=—' }}"
                                     class="w-10 h-10 rounded object-cover" alt="">
                                {{ $product->name }}
                            </td>
                            <td class="p-3 text-gray-500">{{ $product->slug }}</td>
                            <td class="p-3">{{ $product->variants_count }}</td>
                            <td class="p-3">
                                @if ($product->is_active)
                                    <span class="text-green-600">Yes</span>
                                @else
                                    <span class="text-gray-400">No</span>
                                @endif
                            </td>
                            <td class="p-3 text-right space-x-3">
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600">Edit</a>
                                <a href="{{ route('products.show', $product) }}" class="text-gray-500" target="_blank">View</a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this product?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-gray-500">No products yet. Create one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    </div>
</x-app-layout>
