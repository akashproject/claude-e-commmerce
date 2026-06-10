<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin · Orders</h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-700">{{ session('success') }}</div>
        @endif

        <form method="GET" class="mb-4 flex gap-2">
            <select name="status" class="border-gray-300 rounded text-sm">
                <option value="">All statuses</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button class="px-3 py-1 bg-gray-800 text-white rounded text-sm">Filter</button>
        </form>

        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">Order</th>
                        <th class="p-3 text-left">Customer</th>
                        <th class="p-3 text-left">Items</th>
                        <th class="p-3 text-left">Total</th>
                        <th class="p-3 text-left">Payment</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="p-3 font-medium">{{ $order->order_number }}</td>
                            <td class="p-3">{{ $order->user?->name ?? 'Guest' }}</td>
                            <td class="p-3">{{ $order->items_count }}</td>
                            <td class="p-3">&#8377;{{ number_format((float) $order->total, 2) }}</td>
                            <td class="p-3 text-gray-500">{{ $order->payment_status }}</td>
                            <td class="p-3"><x-order-status :status="$order->status" /></td>
                            <td class="p-3 text-right"><a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600">Manage</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-6 text-center text-gray-500">No orders.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>
</x-app-layout>
