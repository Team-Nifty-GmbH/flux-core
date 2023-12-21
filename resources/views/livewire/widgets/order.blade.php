<div x-data="{
    order: $wire.entangle('order', true),
    orderPositions: $wire.entangle('orderPositions', true),
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
                <x-button outline icon="eye" href="{{ route('orders.id', $order['id']) }}">
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
    <div class="w-full pb-2" x-show="orderPositions.length > 0" x-collapse x-cloak>
        <x-table>
            <x-slot:header>
                <x-table.head-cell>
                    {{ __('Name') }}
                </x-table.head-cell>
                <x-table.head-cell>
                    {{ __('Amount') }}
                </x-table.head-cell>
                <x-table.head-cell>
                    {{ __('Total Net Price') }}
                </x-table.head-cell>
            </x-slot:header>
            <template x-for="orderPosition in orderPositions">
                <x-table.row>
                    <x-table.cell x-html="orderPosition.name"></x-table.cell>
                    <x-table.cell class="text-right" x-html="window.formatters.float(orderPosition.amount)"></x-table.cell>
                    <x-table.cell class="text-right" x-html="window.formatters.coloredMoney(orderPosition.total_net_price)"></x-table.cell>
                </x-table.row>
            </template>
        </x-table>
    </div>
    <x-button spinner primary x-on:click="orderPositions.length < 1 ? $wire.loadOrderPositions() : orderPositions = []">
        <span x-text="orderPositions.length < 1 ? '{{ __('Show') }}' : '{{ __('Hide') }}'"></span>
    </x-button>
</div>
