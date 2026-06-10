<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin · Order {{ $order->order_number }}</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="p-3 rounded bg-green-100 text-green-700">{{ session('success') }}</div>
        @endif

        {{-- Status update --}}
        <div class="bg-white shadow sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-gray-500 text-sm">Customer: {{ $order->user?->name ?? 'Guest' }}</p>
                    <p class="text-gray-500 text-sm">Payment: {{ $order->payment_status }} ({{ $order->payment_gateway }})</p>
                </div>
                <x-order-status :status="$order->status" />
            </div>

            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" class="flex gap-2">
                @csrf @method('PATCH')
                <select name="status" class="border-gray-300 rounded text-sm">
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button class="px-4 py-1 bg-indigo-600 text-white rounded text-sm">Update status</button>
            </form>
        </div>

        {{-- Items --}}
        <div class="bg-white shadow sm:rounded-lg divide-y">
            @foreach ($order->items as $item)
                <div class="p-4 flex justify-between">
                    <div>
                        <p class="font-medium">{{ $item->product_name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ collect($item->variant_attributes)->map(fn ($v, $k) => "$k: $v")->join(', ') }} · {{ $item->sku }}
                        </p>
                    </div>
                    <p>{{ $item->quantity }} × &#8377;{{ number_format((float) $item->unit_price, 2) }}</p>
                </div>
            @endforeach
            <div class="p-4 flex justify-between font-semibold">
                <span>Total</span><span>&#8377;{{ number_format((float) $order->total, 2) }}</span>
            </div>
        </div>
    </div>
</x-app-layout>
