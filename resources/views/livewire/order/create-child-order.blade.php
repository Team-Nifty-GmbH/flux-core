<div
    x-data="{
    calculatePositionTotal(position) {
        if (! position || typeof position.is_net === 'undefined') {
            return 0;
        }

        const unitPrice = position.is_net ? position.unit_net_price : position.unit_gross_price;

        return parseFloat(position.amount || 0) * parseFloat(unitPrice || 0);
    },
    getTotalAmount() {
        if (! $wire.replicateOrder?.order_positions) {
            return 0;
        }

        return $wire.replicateOrder.order_positions.reduce((total, position) => {
            return total + this.calculatePositionTotal(position);
        }, 0);
    }
}"
>
    <div class="mx-auto max-w-screen-2xl px-4 pb-6 sm:px-6 lg:px-8">
        <div
            class="mx-auto pb-6 md:flex md:items-center md:justify-between md:space-x-5"
        >
            <div class="flex items-center gap-5">
                <x-avatar
                    xl
                    :image="data_get($parentOrder, 'avatarUrl', '')"
                />
                <div>
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-gray-50"
                    >
                        {{ $this->getTitle() }}
                    </h1>
                    <p
                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                    >
                        {{ __("Parent Order") }}:
                        {{ data_get($parentOrder, "order_number") }} •
                        {{ data_get($parentOrder, "address_invoice.name") }}
                    </p>
                </div>
            </div>
        </div>

        <x-card>
            <div class="mb-6">
                <x-select.styled
                    wire:model="replicateOrder.order_type_id"
                    :options="$availableOrderTypes"
                    :label="__('Select Order Type')"
                    select="label:name|value:id"
                    required
                />
                @error('replicateOrder.order_type_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <h3 class="text-md mb-4 font-semibold">
                        {{ __("Available Positions") }}
                    </h3>
                    <div class="overflow-hidden rounded-lg border">
                        <livewire:order.replicate-order-position-list
                            :order-id="$orderId"
                            :already-taken-positions="array_column($replicateOrder->order_positions, 'id')"
                            wire:model="selectedPositions"
                            lazy
                        />
                    </div>
                    <div class="mt-4 flex justify-end">
                        <x-button
                            color="indigo"
                            :text="__('Take')"
                            x-show="$wire.selectedPositions.length > 0"
                            x-cloak
                            wire:click="takeOrderPositions"
                        />
                    </div>
                </div>

                <div>
                    <h3 class="text-md mb-4 font-semibold">
                        {{ __("Selected Positions") }}
                    </h3>
                    <div class="space-y-2">
                        @forelse ($replicateOrder->order_positions as $index => $position)
                            <x-flux::list-item :item="[]">
                                <x-slot:value>
                                    <div
                                        class="flex w-full items-start justify-between"
                                    >
                                        <div class="flex-1">
                                            <span>
                                                {{ data_get($position, "name") }}
                                            </span>
                                            <div
                                                class="text-sm text-gray-600 dark:text-gray-400"
                                            >
                                                {!! data_get($position, "description") !!}
                                            </div>
                                        </div>
                                        <div class="ml-4 text-right">
                                            <div class="font-semibold">
                                                <span
                                                    x-text="calculatePositionTotal($wire.replicateOrder.order_positions?.[{{ $index }}] || {}).toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2})"
                                                ></span>
                                                {{ data_get($parentOrder, "currency.symbol") }}
                                            </div>
                                            <div
                                                class="text-sm text-gray-600 dark:text-gray-400"
                                            >
                                                {{ Number::currency(data_get($position, "is_net") ? data_get($position, "unit_net_price") : data_get($position, "unit_gross_price"), data_get($parentOrder, "currency.iso", "EUR"), "de") }}@if (data_get($position, "unit_abbreviation"))/ {{ data_get($position, "unit_abbreviation") }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </x-slot>
                                <x-slot:actions>
                                    <x-number
                                        wire:model.number="replicateOrder.order_positions.{{ $index }}.amount"
                                        min="0"
                                        :max="data_get($position, 'amount', 0)"
                                    />
                                    <x-button
                                        color="red"
                                        icon="trash"
                                        wire:click="removePosition({{ $index }})"
                                    />
                                </x-slot>
                            </x-flux::list-item>
                        @empty
                            <div class="py-8 text-center text-gray-500">
                                {{ __("No positions selected") }}
                            </div>
                        @endforelse
                    </div>

                    <div
                        class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800"
                        x-show="$wire.replicateOrder.order_positions.length > 0"
                        x-cloak
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">
                                {{ __("Total") }}:
                            </span>
                            <span class="text-lg font-bold">
                                <span
                                    x-html="window.formatters.coloredMoney(getTotalAmount(), '{{ data_get($parentOrder, 'currency.symbol') }}')"
                                ></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <x-button
                    color="secondary"
                    light
                    :text="__('Cancel')"
                    :href="route('orders.id', ['id' => $orderId])"
                    wire:navigate
                />
                <x-button
                    color="indigo"
                    :text="$this->getTitle()"
                    wire:click="save"
                    loading
                    x-bind:disabled="!$wire.replicateOrder.order_type_id || !$wire.replicateOrder.order_positions.length"
                />
            </div>
        </x-card>
    </div>
</div>
