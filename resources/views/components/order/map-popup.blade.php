@props([
    'orders' => [],
])

<div class="flex max-h-64 flex-col gap-3 overflow-y-auto">
    @foreach ($orders as $order)
        <div class="border-b border-gray-200 pb-2 last:border-b-0 last:pb-0">
            <div class="font-semibold">
                {{ data_get($order->addressDelivery, 'company') ?? data_get($order->addressDelivery, 'name') }}
            </div>
            <div class="text-sm text-gray-600">
                {{ data_get($order->addressDelivery, 'street') }},
                {{ data_get($order->addressDelivery, 'zip') }}
                {{ data_get($order->addressDelivery, 'city') }}
            </div>
            <div class="text-sm">
                @if ($order->invoice_number)
                    <span class="font-medium">{{ __('Invoice') }}:</span>
                    {{ $order->invoice_number }}
                @else
                    <span class="font-medium">{{ __('Order') }}:</span>
                    {{ $order->order_number }}
                @endif
            </div>
            <div class="text-sm">
                <span class="font-medium">{{ __('Date') }}:</span>
                {{ $order->order_date?->isoFormat('L') }}
            </div>
            @if ($order->contact)
                <div class="text-sm">
                    <span class="font-medium">{{ __('Customer') }}:</span>
                    {{ $order->contact->getLabel() }}
                </div>
            @endif

            <div class="mt-1">
                <a
                    href="{{ $order->detailRoute() }}"
                    wire:navigate
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                >
                    {{ __('Show') }} &rarr;
                </a>
            </div>
        </div>
    @endforeach
</div>
