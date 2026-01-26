@props([
    'orders' => [],
])

<div class="flex max-h-64 flex-col gap-3 overflow-y-auto">
    @foreach ($orders as $item)
        <div class="border-b border-gray-200 pb-2 last:border-b-0 last:pb-0">
            <div class="font-semibold">
                {{ data_get($item, 'address.company') ?? data_get($item, 'address.name') }}
            </div>
            <div class="text-sm text-gray-600">
                {{ data_get($item, 'address.street') }},
                {{ data_get($item, 'address.zip') }}
                {{ data_get($item, 'address.city') }}
            </div>
            <div class="text-sm">
                @if (data_get($item, 'order.invoice_number'))
                    <span class="font-medium">{{ __('Invoice') }}:</span>
                    {{ data_get($item, 'order.invoice_number') }}
                @else
                    <span class="font-medium">{{ __('Order') }}:</span>
                    {{ data_get($item, 'order.order_number') }}
                @endif
            </div>
            <div class="text-sm">
                <span class="font-medium">{{ __('Date') }}:</span>
                {{ data_get($item, 'order.order_date')?->isoFormat('L') }}
            </div>
            @if (data_get($item, 'order.contact'))
                <div class="text-sm">
                    <span class="font-medium">{{ __('Customer') }}:</span>
                    {{ data_get($item, 'order.contact')->getLabel() }}
                </div>
            @endif

            <div class="mt-1">
                <a
                    href="{{ data_get($item, 'order')?->detailRoute() }}"
                    wire:navigate
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                >
                    {{ __('Show') }} &rarr;
                </a>
            </div>
        </div>
    @endforeach
</div>
