<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Orders</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">Order</th>
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-left">Items</th>
                        <th class="p-3 text-left">Total</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="p-3 font-medium">{{ $order->order_number }}</td>
                            <td class="p-3 text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                            <td class="p-3">{{ $order->items_count }}</td>
                            <td class="p-3">&#8377;{{ number_format((float) $order->total, 2) }}</td>
                            <td class="p-3"><x-order-status :status="$order->status" /></td>
                            <td class="p-3 text-right">
                                <a href="{{ route('dashboard.order', $order) }}" class="text-indigo-600">Track</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-gray-500">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>
</x-app-layout>
