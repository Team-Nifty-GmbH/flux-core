<div
    x-data="{
        order: $wire.entangle('order', true),
        orderPositions: $wire.entangle('orderPositions', true),
        formatter: @js(resolve_static(\FluxErp\Models\Order::class, "typeScriptAttributes")),
    }"
>
    <x-slot:header>
        <div
            class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0"
        >
            <div class="flex">
                <x-avatar squared :image="$order['avatar']" />
                <div class="pl-2">
                    <div
                        class="text-sm font-semibold text-gray-900 dark:text-gray-50"
                    >
                        {{ $order["order_number"] }}
                        {{ $order["address_invoice"]["label"] }}
                    </div>
                    <x-label class="opacity-60">
                        {{ __($order["order_type"]["name"]) }}
                    </x-label>
                </div>
            </div>
            <div class="pl-2">
                <x-button
                    color="secondary"
                    light
                    outline
                    icon="eye"
                    href="{{ route('orders.id', $order['id']) }}"
                ></x-button>
            </div>
        </div>
    </x-slot>
    <div class="pb-2 font-semibold uppercase">{{ __("General") }}</div>
    <div class="grid grid-cols-2 gap-2">
        <x-label :label="__('Customer')" />
        <div
            class="block text-sm font-medium text-gray-700 sm:mt-px dark:text-gray-50"
            x-text="order.address_invoice.name"
        ></div>
        <x-label :label="__('Invoice Address')" />
        <div
            class="block text-sm font-medium text-gray-700 sm:mt-px dark:text-gray-50"
            x-text="order.address_invoice.description"
        ></div>
        <x-label :label="__('Order state')" />
        <div
            class="block text-sm font-medium text-gray-700 sm:mt-px dark:text-gray-50"
            x-html="formatters.state(order.state, formatter.state[1])"
        ></div>
        <x-label :label="__('Commission')" />
        <div
            class="block text-sm font-medium text-gray-700 sm:mt-px dark:text-gray-50"
            x-text="order.commission"
        ></div>
        <x-label :label="__('Invoice number')" />
        <div
            class="block text-sm font-medium text-gray-700 sm:mt-px dark:text-gray-50"
            x-text="order.invoice_number"
        ></div>
    </div>
    <div class="pb-2 pt-8 font-semibold uppercase">{{ __("Accounting") }}</div>
    <div class="grid grid-cols-2 gap-2">
        <x-label :label="__('Payment state')" />
        <div
            class="block text-sm font-medium text-gray-700 sm:mt-px dark:text-gray-50"
            x-html="formatters.state(order.payment_state, formatter.payment_state[1])"
        ></div>
        <x-label :label="__('Total net')" />
        <div
            class="block text-sm font-medium text-gray-700 sm:mt-px dark:text-gray-50"
            x-text="formatters.money(order.total_net_price, order.currency)"
        ></div>
    </div>
    <div class="pb-2 pt-8 font-semibold uppercase">
        {{ __("Order positions") }}
    </div>
    <div
        class="w-full pb-2"
        x-show="orderPositions.length > 0"
        x-collapse
        x-cloak
    >
        <x-flux::table>
            <x-slot:header>
                <x-flux::table.head-cell>
                    {{ __("Name") }}
                </x-flux::table.head-cell>
                <x-flux::table.head-cell>
                    {{ __("Amount") }}
                </x-flux::table.head-cell>
                <x-flux::table.head-cell>
                    {{ __("Total Net Price") }}
                </x-flux::table.head-cell>
            </x-slot>
            <template x-for="orderPosition in orderPositions">
                <x-flux::table.row>
                    <x-flux::table.cell
                        x-html="orderPosition.name"
                    ></x-flux::table.cell>
                    <x-flux::table.cell
                        class="text-right"
                        x-html="window.formatters.float(orderPosition.amount)"
                    ></x-flux::table.cell>
                    <x-flux::table.cell
                        class="text-right"
                        x-html="window.formatters.coloredMoney(orderPosition.total_net_price)"
                    ></x-flux::table.cell>
                </x-flux::table.row>
            </template>
        </x-flux::table>
    </div>
    <x-button
        loading
        color="indigo"
        x-on:click="orderPositions.length < 1 ? $wire.loadOrderPositions() : orderPositions = []"
    >
        <span
            x-text="orderPositions.length < 1 ? '{{ __("Show") }}' : '{{ __("Hide") }}'"
        ></span>
    </x-button>
</div>
