<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Checkout @if ($mode === 'buy_now') <span class="text-sm text-indigo-600">(Buy Now)</span> @endif
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 grid md:grid-cols-3 gap-6"
         x-data="{ billingSame: true }">

        {{-- Order summary --}}
        <div class="md:col-span-1 bg-white shadow sm:rounded-lg p-4 h-fit">
            <h3 class="font-semibold mb-3">Summary</h3>
            @foreach ($lines as $line)
                <div class="flex justify-between text-sm py-1">
                    <span>{{ $line['variant']->product->name }} ({{ $line['variant']->label() }}) × {{ $line['quantity'] }}</span>
                    <span>&#8377;{{ number_format($line['variant']->price * $line['quantity'], 2) }}</span>
                </div>
            @endforeach
            <hr class="my-2">
            <div class="flex justify-between font-semibold">
                <span>Total</span><span>&#8377;{{ number_format($subtotal, 2) }}</span>
            </div>
        </div>

        {{-- Address + pay --}}
        <form action="{{ route('checkout.place') }}" method="POST" class="md:col-span-2 bg-white shadow sm:rounded-lg p-6 space-y-4">
            @csrf
            <input type="hidden" name="mode" value="{{ $mode }}">

            @if ($errors->any())
                <div class="p-3 rounded bg-red-100 text-red-700 text-sm">
                    <ul class="list-disc ml-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <h3 class="font-semibold">Shipping details</h3>
            <div class="grid grid-cols-2 gap-3">
                <input name="shipping[name]" placeholder="Full name" value="{{ old('shipping.name', auth()->user()?->name) }}" class="border-gray-300 rounded" required>
                <input name="shipping[email]" type="email" placeholder="Email" value="{{ old('shipping.email', auth()->user()?->email) }}" class="border-gray-300 rounded" required>
                <input name="shipping[phone]" placeholder="Phone" value="{{ old('shipping.phone') }}" class="border-gray-300 rounded" required>
                <input name="shipping[postcode]" placeholder="Postcode" value="{{ old('shipping.postcode') }}" class="border-gray-300 rounded" required>
                <input name="shipping[line1]" placeholder="Address line 1" value="{{ old('shipping.line1') }}" class="border-gray-300 rounded col-span-2" required>
                <input name="shipping[line2]" placeholder="Address line 2 (optional)" value="{{ old('shipping.line2') }}" class="border-gray-300 rounded col-span-2">
                <input name="shipping[city]" placeholder="City" value="{{ old('shipping.city') }}" class="border-gray-300 rounded" required>
                <input name="shipping[state]" placeholder="State" value="{{ old('shipping.state') }}" class="border-gray-300 rounded" required>
                <input name="shipping[country]" placeholder="Country (2-letter, e.g. IN)" maxlength="2" value="{{ old('shipping.country', 'IN') }}" class="border-gray-300 rounded" required>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="billing_same" value="1" x-model="billingSame" checked>
                Billing address same as shipping
            </label>

            <div x-show="!billingSame" class="grid grid-cols-2 gap-3">
                <input name="billing[name]" placeholder="Billing name" class="border-gray-300 rounded col-span-2">
                <input name="billing[line1]" placeholder="Billing address" class="border-gray-300 rounded col-span-2">
                <input name="billing[city]" placeholder="City" class="border-gray-300 rounded">
                <input name="billing[state]" placeholder="State" class="border-gray-300 rounded">
                <input name="billing[postcode]" placeholder="Postcode" class="border-gray-300 rounded">
                <input name="billing[country]" placeholder="Country" maxlength="2" class="border-gray-300 rounded">
            </div>

            <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded font-medium">
                Pay &#8377;{{ number_format($subtotal, 2) }}
            </button>
            <p class="text-xs text-gray-400 text-center">
                Gateway: {{ config('services.payment_driver') }} — a "fake" gateway lets you simulate success/failure locally.
            </p>
        </form>
    </div>
</x-app-layout>
