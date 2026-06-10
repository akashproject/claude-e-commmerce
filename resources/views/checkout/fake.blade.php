<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Simulated Payment</h2>
    </x-slot>

    <div class="py-8 max-w-md mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6 text-center space-y-4">
            <p class="text-gray-600">Order <strong>{{ $order->order_number }}</strong></p>
            <p class="text-2xl font-bold">&#8377;{{ number_format((float) $order->total, 2) }}</p>
            <p class="text-sm text-gray-400">FakeGateway — choose an outcome to simulate the payment provider.</p>

            <form action="{{ route('checkout.fake.decision', $order) }}" method="POST" class="flex gap-3 justify-center">
                @csrf
                <button name="decision" value="success" class="px-5 py-2 bg-green-600 text-white rounded">Pay (success)</button>
                <button name="decision" value="fail" class="px-5 py-2 bg-red-600 text-white rounded">Cancel (fail)</button>
            </form>
        </div>
    </div>
</x-app-layout>
