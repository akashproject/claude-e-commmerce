<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Order {{ $order->order_number }}</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
        {{-- Status tracker --}}
        <div class="bg-white shadow sm:rounded-lg p-6">
            <div class="flex items-center justify-between">
                <span class="text-gray-500 text-sm">Current status</span>
                <x-order-status :status="$order->status" />
            </div>
            @php
                $flow = ['pending', 'paid', 'processing', 'shipped', 'delivered'];
                $currentIndex = array_search($order->status, $flow, true);
            @endphp
            @if ($order->status !== 'cancelled')
                <ol class="mt-4 flex items-center w-full text-xs">
                    @foreach ($flow as $i => $step)
                        <li class="flex-1 flex flex-col items-center">
                            <span @class([
                                'w-6 h-6 rounded-full flex items-center justify-center text-white',
                                'bg-indigo-600' => $currentIndex !== false && $i <= $currentIndex,
                                'bg-gray-300'   => $currentIndex === false || $i > $currentIndex,
                            ])>{{ $i + 1 }}</span>
                            <span class="mt-1 text-gray-500">{{ ucfirst($step) }}</span>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="mt-4 text-red-600 text-sm">This order was cancelled.</p>
            @endif
        </div>

        {{-- Items --}}
        <div class="bg-white shadow sm:rounded-lg divide-y">
            @foreach ($order->items as $item)
                <div class="p-4 flex justify-between">
                    <div>
                        <p class="font-medium">{{ $item->product_name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ collect($item->variant_attributes)->map(fn ($v, $k) => "$k: $v")->join(', ') }}
                            · {{ $item->sku }}
                        </p>
                        <p class="text-sm text-gray-500">Qty {{ $item->quantity }} × &#8377;{{ number_format((float) $item->unit_price, 2) }}</p>
                    </div>
                    <p class="font-medium">&#8377;{{ number_format((float) $item->line_total, 2) }}</p>
                </div>
            @endforeach
            <div class="p-4 flex justify-between font-semibold">
                <span>Total</span><span>&#8377;{{ number_format((float) $order->total, 2) }}</span>
            </div>
        </div>

        {{-- Shipping --}}
        <div class="bg-white shadow sm:rounded-lg p-6 text-sm text-gray-600">
            <h3 class="font-semibold text-gray-800 mb-2">Shipping to</h3>
            <p>{{ $order->shipping_address['name'] ?? '' }}</p>
            <p>{{ $order->shipping_address['line1'] ?? '' }} {{ $order->shipping_address['line2'] ?? '' }}</p>
            <p>{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} {{ $order->shipping_address['postcode'] ?? '' }}</p>
            <p>{{ $order->shipping_address['country'] ?? '' }}</p>
        </div>
    </div>
</x-app-layout>
