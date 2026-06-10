<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin · Attributes</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="p-3 rounded bg-green-100 text-green-700">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="p-3 rounded bg-red-100 text-red-700 text-sm">
                <ul class="list-disc ml-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- New attribute --}}
        <form action="{{ route('admin.attributes.store') }}" method="POST"
              class="bg-white shadow sm:rounded-lg p-6 flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Attribute name</label>
                <input name="name" placeholder="e.g. Color" required class="mt-1 border-gray-300 rounded">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" class="mt-1 border-gray-300 rounded">
                    <option value="select">Select</option>
                    <option value="swatch">Swatch</option>
                </select>
            </div>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">Add attribute</button>
        </form>

        {{-- Existing attributes + values --}}
        @forelse ($attributes as $attribute)
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold">{{ $attribute->name }}
                        <span class="text-xs text-gray-400">({{ $attribute->type }})</span>
                    </h3>
                    <form action="{{ route('admin.attributes.destroy', $attribute) }}" method="POST"
                          onsubmit="return confirm('Delete attribute and all its values?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 text-xs">Delete attribute</button>
                    </form>
                </div>

                <div class="flex flex-wrap gap-2 mt-3">
                    @forelse ($attribute->values as $value)
                        <span class="inline-flex items-center gap-2 px-2 py-1 bg-gray-100 rounded text-sm">
                            @if ($value->swatch)
                                <span class="w-3 h-3 rounded-full inline-block" style="background: {{ $value->swatch }}"></span>
                            @endif
                            {{ $value->value }}
                            <form action="{{ route('admin.attributes.values.destroy', $value) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-red-500">×</button>
                            </form>
                        </span>
                    @empty
                        <span class="text-gray-400 text-sm">No values yet.</span>
                    @endforelse
                </div>

                {{-- Add value --}}
                <form action="{{ route('admin.attributes.values.store', $attribute) }}" method="POST"
                      class="mt-3 flex items-end gap-2">
                    @csrf
                    <input name="value" placeholder="New value (e.g. Red)" required class="border-gray-300 rounded text-sm">
                    <input name="swatch" placeholder="#hex (optional)" class="border-gray-300 rounded text-sm w-32">
                    <button class="px-3 py-1 bg-gray-800 text-white rounded text-sm">Add value</button>
                </form>
            </div>
        @empty
            <p class="text-gray-500">No attributes defined yet.</p>
        @endforelse
    </div>
</x-app-layout>
