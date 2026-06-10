<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin · New Product</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data"
              class="bg-white shadow sm:rounded-lg p-6 space-y-4">
            @csrf

            @if ($errors->any())
                <div class="p-3 rounded bg-red-100 text-red-700 text-sm">
                    <ul class="list-disc ml-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input name="name" value="{{ old('name') }}" required
                       class="mt-1 w-full border-gray-300 rounded">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="4" class="mt-1 w-full border-gray-300 rounded">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Base image</label>
                <input type="file" name="image" accept="image/*" class="mt-1 w-full text-sm">
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" checked> Active (visible in store)
            </label>

            <div class="flex gap-3">
                <button class="px-5 py-2 bg-indigo-600 text-white rounded">Create &amp; add variants</button>
                <a href="{{ route('admin.products.index') }}" class="px-5 py-2 border border-gray-300 rounded">Cancel</a>
            </div>
            <p class="text-xs text-gray-400">After creating the product you'll be taken to the variant generator.</p>
        </form>
    </div>
</x-app-layout>
