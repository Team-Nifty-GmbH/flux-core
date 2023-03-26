<div x-data="{
    order: $wire.entangle('order'),
    orderPositions: $wire.entangle('orderPositions'),
    formatter: @js(\FluxErp\Models\Order::typeScriptAttributes()),
}">
    <x-slot:header>
        <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
            <div class="flex">
                <x-avatar squared :src="$order['avatar']" />
                <div class="pl-2">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-50">
                        {{ $order['order_number'] }} {{ $order['address_invoice']['label'] }}
                    </div>
                    <x-label class="opacity-60">
                        {{ __($order['order_type']['name']) }}
                    </x-label>
                </div>
            </div>
            <div class="pl-2">
                <x-button outline icon="eye" href="{{ route('orders.id?', $order['id']) }}">
                </x-button>
            </div>
        </div>
    </x-slot:header>
    <div class="pb-2 font-semibold uppercase">{{ __('General') }}</div>
    <div class="grid grid-cols-2 gap-2">
        <x-label>
            {{ __('Customer') }}
        </x-label>
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-text="order.address_invoice.name">
        </div>
        <x-label>
            {{ __('Invoice Address') }}
        </x-label>
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-text="order.address_invoice.description">
        </div>
        <x-label>
            {{ __('Order state') }}
        </x-label>
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-html="formatters.state(order.state, formatter.state[1])">
        </div>
        <x-label>
            {{ __('Commission') }}
        </x-label>
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-text="order.commission">
        </div>
        <x-label>
            {{ __('Invoice number') }}
        </x-label>
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-text="order.invoice_number">
        </div>
    </div>
    <div class="pt-8 pb-2 font-semibold uppercase">{{ __('Accounting') }}</div>
    <div class="grid grid-cols-2 gap-2">
        <x-label>
            {{ __('Payment state') }}
        </x-label>
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-html="formatters.state(order.payment_state, formatter.payment_state[1])">
        </div>
        <x-label>
            {{ __('Total net') }}
        </x-label>
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-text="formatters.money(order.total_net_price, order.currency)">
        </div>
    </div>
    <div class="pt-8 pb-2 font-semibold uppercase">{{ __('Order positions') }}</div>
    <div x-show="orderPositions.length > 0" x-cloak x-transition>
        <x-alpinejs-table
            :indented-cols="['name']"
            :stretch-col="['name']"
            :model="\FluxErp\Models\OrderPosition::class"
            x-disabled="record.is_bundle_position"
            x-bind:class.tr="{'opacity-50 sortable-filter': record.is_bundle_position}"
            :enabled-cols="['name', 'amount', 'total_net_price']"
            :available-cols="['name', 'product_number', 'amount', 'total_net_price']"
            wire:model="orderPositions"
            x-bind:class.td="{
                        'bg-gray-200 dark:bg-secondary-700 font-bold': (record.is_free_text && record.depth === 0 && record.has_children),
                        'opacity-90': record.is_alternative,
                        'opacity-50 sortable-filter': record.is_bundle_position,
                        'font-semibold': record.is_free_text
                    }"
        />
    </div>
    <x-button x-show="orderPositions.length < 1" spinner primary x-on:click="$wire.loadOrderPositions()">
        {{ __('Show') }}
    </x-button>
</div>
