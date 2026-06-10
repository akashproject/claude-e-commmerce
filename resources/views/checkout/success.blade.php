<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Order Confirmed</h2>
    </x-slot>

    <div class="py-8 max-w-lg mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6 text-center space-y-3">
            <div class="text-5xl">✅</div>
            <h3 class="text-xl font-semibold">Thank you!</h3>
            <p class="text-gray-600">Order <strong>{{ $order->order_number }}</strong> is {{ $order->status }}.</p>
            <p class="text-2xl font-bold">&#8377;{{ number_format((float) $order->total, 2) }}</p>
            @auth
                <a href="{{ route('dashboard.order', $order) }}" class="inline-block mt-2 px-5 py-2 bg-indigo-600 text-white rounded">Track Order</a>
            @endauth
            <div><a href="{{ route('home') }}" class="text-indigo-600 text-sm">Continue shopping</a></div>
        </div>
    </div>
</x-app-layout>
