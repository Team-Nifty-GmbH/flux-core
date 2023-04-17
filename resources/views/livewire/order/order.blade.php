<div
    x-data="{
        order: $wire.entangle('order').defer,
        tab: $wire.entangle('tab'),
        formatter: @js(\FluxErp\Models\Order::typeScriptAttributes()),
        orderPositions: [],
        createDocuments: false,
    }"
    x-on:updated-order-positions.window="orderPositions = $event.detail"
    x-on:order-positions-updated="$wire.set('hasUpdatedOrderPositions', true)"
    x-init="() => {
        var meta = document.createElement('meta');
        meta.name = 'currency-code';
        meta.content = order.currency.iso;
        document.getElementsByTagName('head')[0].appendChild(meta);
     }"
>
    <x-modal.card id="preview" :fullscreen="true"  :title="__('Preview')">
        <iframe id="preview-iframe" src="#" loading="lazy" class="w-full min-h-screen"></iframe>
        <x-slot name="footer">
            <div class="flex justify-end gap-x-4">
                <div class="flex">
                    <x-button flat label="Cancel" x-on:click="close" />
                    <x-button primary label="Save" wire:click="save" />
                </div>
            </div>
        </x-slot>
    </x-modal.card>
    <x-sidebar x-show="createDocuments">
        @foreach($printLayouts as $key => $printLayout)
            <x-checkbox wire:model.defer="selectedPrintLayouts.{{ $key }}" :label="$key" />
        @endforeach
        <x-slot name="footer">
            <x-button spinner primary x-on:click="$wire.downloadDocuments()">
                Download
            </x-button>
        </x-slot>
    </x-sidebar>
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5">
        <div class="flex items-center space-x-5">
            <x-avatar xl :src="$order['contact']['avatar_url'] ?? ''"></x-avatar>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex">
                        <x-heroicons x-show="order.is_locked" variant="solid" name="lock-closed" />
                        <x-heroicons x-show="! order.is_locked" variant="solid" name="lock-open" />
                        <div class="pl-2">
                            <span class="opacity-40 transition-opacity hover:opacity-100" x-text="order.order_type.name">
                            </span>
                            <span class="opacity-40 transition-opacity hover:opacity-100" x-text="order.invoice_number ? order.invoice_number : (order.order_number || order.id)">
                            </span>
                        </div>
                    </div>
                    <span x-text="order.address_invoice.description"></span>
                </h1>
            </div>
        </div>
        <div class="justify-stretch mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
            @if(user_can('api.orders.{id}.delete') && $order['id'] && ! $order['is_locked'])
                <x-button negative label="{{ __('Delete') }}" x-on:click="
                              window.$wireui.confirmDialog({
                              title: '{{ __('Delete order') }}',
                    description: '{{ __('Do you really want to delete this order?') }}',
                    icon: 'error',
                    accept: {
                        label: '{{ __('Delete') }}',
                        method: 'delete',
                    },
                    reject: {
                        label: '{{ __('Cancel') }}',
                    }
                    }, '{{ $this->id }}')
                    "/>
            @endif
            <x-button
                primary
                spinner
                class="w-full"
                x-on:click="$wire.save(orderPositions)"
                :label="__('Save')"
            />
        </div>
    </div>
    <x-tabs
        wire:model="tab"
        :tabs="[
                    'order-positions' => __('Order positions'),
                    'attachments' => __('Attachments'),
                    'accounting' => __('Accounting'),
                    'comments' => __('Comments'),
                    'related' => __('Related processes'),
                ]"
    >
        <div class="w-full lg:col-start-1 xl:col-span-2 xl:flex xl:space-x-6">
            <section class="relative basis-2/12" wire:ignore>
                <div class="sticky top-6 space-y-6">
                    <x-card>
                        <x-slot:header>
                            <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                                <x-label>
                                    {{ __('Invoice Address') }}
                                </x-label>
                                <div class="pl-2">
                                    <x-button outline icon="eye" href="{{ route('contacts.id?', $order['address_invoice']['contact_id'] ?? '') }}">
                                    </x-button>
                                </div>
                            </div>
                        </x-slot:header>
                        <x-select
                            :disabled="$order['is_locked']"
                            class="pb-4"
                            wire:model="order.address_invoice_id"
                            option-value="id"
                            option-label="label"
                            option-description="description"
                            :clearable="false"
                            :async-data="[
                                'api' => route('search', \FluxErp\Models\Address::class),
                                'params' => [
                                    'with' => 'contact.media',
                                    'where' => [
                                        ['contact_id', '=', $order['contact_id']],
                                    ],
                                ]
                            ]"
                            />
                        <div class="text-sm">
                            <div x-text="order.address_invoice.company">
                            </div>
                            <div x-text="(order.address_invoice.firstname + ' ' + order.address_invoice.lastname).trim()">
                            </div>
                            <div x-text="order.address_invoice.street">
                            </div>
                            <div x-text="(order.address_invoice.zip + ' ' + order.address_invoice.city).trim()">
                            </div>
                        </div>
                    </x-card>
                    <x-card>
                        <x-slot:header>
                            <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                                <x-label>
                                    {{ __('Delivery Address') }}
                                </x-label>
                                <div class="pl-2">
                                    <x-button outline icon="eye" href="{{ route('contacts.id?', $order['address_delivery']['contact_id'] ?? '') }}">
                                    </x-button>
                                </div>
                            </div>
                        </x-slot:header>
                        <x-select
                            :disabled="$order['is_locked']"
                            class="pb-4"
                            wire:model="order.address_delivery_id"
                            option-value="id"
                            option-label="label"
                            option-description="description"
                            :clearable="false"
                            :async-data="[
                                'api' => route('search', \FluxErp\Models\Address::class),
                                'params' => [
                                    'with' => 'contact.media',
                                    'where' => [
                                        ['contact_id', '=', $order['contact_id']],
                                    ],
                                ]
                            ]" />
                        <div class="text-sm" x-bind:class="order.address_delivery_id === order.address_invoice_id && 'hidden'">
                            <div x-text="order.address_delivery.company">
                            </div>
                            <div x-text="(order.address_delivery.firstname + ' ' + order.address_delivery.lastname).trim()">
                            </div>
                            <div x-text="order.address_delivery.street">
                            </div>
                            <div x-text="(order.address_delivery.zip + ' ' + order.address_delivery.city).trim()">
                            </div>
                        </div>
                    </x-card>
                    <x-card>
                        <div class="space-y-3">
                            <x-select
                                disabled
                                :label="__('Client')"
                                :options="$clients"
                                option-value="id"
                                option-label="name"
                                :clearable="false"
                                autocomplete="off"
                                wire:model="order.client_id"
                            />
                            <x-select
                                :label="__('Price list')"
                                :options="$priceLists"
                                option-value="id"
                                option-label="name"
                                :clearable="false"
                                autocomplete="off"
                                wire:model="order.price_list_id"
                                x-bind:disabled="order.is_locked"
                            />
                            <x-select
                                :label="__('Payment method')"
                                :options="$paymentTypes"
                                option-value="id"
                                option-label="name"
                                :clearable="false"
                                autocomplete="off"
                                wire:model="order.payment_type_id"
                                x-bind:disabled="order.is_locked"
                            />
                            <x-select
                                :label="__('Language')"
                                :options="$languages"
                                option-value="id"
                                option-label="name"
                                :clearable="false"
                                autocomplete="off"
                                wire:model.defer="order.language_id"
                                x-bind:disabled="order.is_locked"
                            />
                        </div>
                    </x-card>
                    <x-card>
                        <div class="space-y-3">
                            <x-state
                                class="w-full"
                                align="left"
                                :label="__('Order state')"
                                wire:model="order.state"
                                formatters="formatter.state"
                                avialable="availableStates.state"
                            />
                            <x-state
                                align="left"
                                :label="__('Payment state')"
                                wire:model="order.payment_state"
                                formatters="formatter.payment_state"
                                avialable="availableStates.payment_state"
                            />
                            <x-state
                                align="left"
                                :label="__('Delivery state')"
                                wire:model="order.delivery_state"
                                formatters="formatter.delivery_state"
                                avialable="availableStates.delivery_state"
                            />
                        </div>
                    </x-card>
                </div>
            </section>
            <section class="basis-8/12 pt-6 lg:pt-0">
                <livewire:is :id="$order['id'] ?? null" :component="'order.' . $tab" wire:key="{{ uniqid() }}" />
            </section>
            <section class="relative basis-2/12" wire:ignore>
                <div class="sticky top-6 space-y-6">
                    <x-card>
                        <div class="space-y-4">
                            @if($printLayouts)
                                <x-button
                                    primary
                                    class="w-full"
                                    x-on:click="createDocuments = true"
                                    :label="__('Send documents')"
                                />
                                <x-button
                                    class="w-full"
                                    x-on:click="createDocuments = true"
                                    :label="__('Download documents')"
                                />
                                <x-button
                                    class="w-full"
                                    x-on:click="createDocuments = true"
                                    :label="__('Print documents')"
                                />
                                <div class="dropdown-full-w">
                                    <x-dropdown width="w-full">
                                        <x-slot name="trigger">
                                            <x-button class="w-full">
                                                {{ __('Preview') }}
                                            </x-button>
                                        </x-slot>
                                        @foreach($printLayouts as $key => $printLayout)
                                            <x-dropdown.item
                                                x-on:click="const preview = document.getElementById('preview'); document.getElementById('preview-iframe').src = '{{ route('print.render', ['id' => $order['id'], 'view' => $key, 'model' => \FluxErp\Models\Order::class, '']) }}'; $openModal(preview)">
                                                {{ $key }}
                                            </x-dropdown.item>
                                        @endforeach
                                    </x-dropdown>
                                </div>
                            @endif
                            <livewire:features.custom-events :model="\FluxErp\Models\Order::class" :id="$order['id']" />
                        </div>
                    </x-card>
                    <x-card>
                        <div class="text-sm">
                            <div class="flex justify-between py-2.5">
                                <div>
                                    {{ __('Sum net') }}
                                </div>
                                <div>
                                    <span x-currency="{value: order.total_net_price, currency: order.currency.iso}">
                                    </span>
                                </div>
                            </div>
                            <template x-for="vat in order.total_vats">
                                <div class="flex justify-between py-2.5">
                                    <div>
                                        <span>{{ __('Plus ') }}</span>
                                        <span x-percentage="vat.vat_rate_percentage">
                                        </span>
                                    </div>
                                    <div>
                                        <span x-currency="{value: vat.total_vat_price, currency: order.currency.iso}">
                                        </span>
                                    </div>
                                </div>
                            </template>
                            <div class="dark:bg-secondary-700 flex justify-between bg-gray-50 py-2.5">
                                <div>
                                    {{ __('Total gross') }}
                                </div>
                                <div>
                                    <span x-currency="{value: order.total_gross_price, currency: order.currency.iso}">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </x-card>
                    <x-card>
                        <div class="space-y-3">
                            <x-input wire:model.defer="order.commission" :label="__('Commission')" />
                        </div>
                    </x-card>
                </div>
            </section>
        </div>
    </x-tabs>
</div>
