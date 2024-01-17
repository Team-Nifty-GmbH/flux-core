<div
    x-data="{
        init() {
            var meta = document.createElement('meta');
            meta.name = 'currency-code';
            meta.content = $wire.order.currency.iso;
            document.getElementsByTagName('head')[0].appendChild(meta);
        },
        orderPositions: [],
        preview: null,
        formatter: @js(\FluxErp\Models\Order::typeScriptAttributes()),
    }"
>
    @section('modals')
        <x-modal.card id="preview" max-width="6xl" :title="__('Preview')">
            <iframe id="preview-iframe" src="#" loading="lazy" class="w-full min-h-screen"></iframe>
            <x-slot:footer>
                <div class="flex justify-end gap-x-4">
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close" />
                        <x-button spinner primary :label="__('Download')" wire:click="downloadPreview(preview)" />
                    </div>
                </div>
            </x-slot:footer>
        </x-modal.card>
        <x-modal name="create-documents">
            <x-card :title="__('Create Documents')">
                <div class="grid grid-cols-4 gap-1.5">
                    <div class="font-semibold text-sm">{{ __('Print') }}</div>
                    <div class="font-semibold text-sm">{{ __('Email') }}</div>
                    <div class="font-semibold text-sm">{{ __('Download') }}</div>
                    <div class="font-semibold text-sm">{{ __('Force Create') }}</div>
                    @foreach($printLayouts as $printLayout)
                        <x-checkbox wire:model.boolean="selectedPrintLayouts.print.{{ $printLayout }}" :label="__($printLayout)" />
                        <x-checkbox wire:model.boolean="selectedPrintLayouts.email.{{ $printLayout }}" :label="__($printLayout)" />
                        <x-checkbox wire:model.boolean="selectedPrintLayouts.download.{{ $printLayout }}" :label="__($printLayout)" />
                        <x-checkbox wire:model.boolean="selectedPrintLayouts.force.{{ $printLayout }}" :label="__($printLayout)" />
                    @endforeach
                </div>
                <x-slot:footer>
                    <div class="flex justify-end gap-x-4">
                        <div class="flex">
                            <x-button flat :label="__('Cancel')" x-on:click="close" />
                            <x-button primary :label="__('Continue')" spinner wire:click="createDocuments().then(() => { close(); });" />
                        </div>
                    </div>
                </x-slot:footer>
            </x-card>
        </x-modal>
        <x-modal name="replicate-order">
            <x-card>
                <section x-data="{
                    updateContactId(id) {
                        Alpine.$data(
                            document.getElementById('invoice-address-id').querySelector('[x-data]')
                        ).asyncData.params.where[0][2] = id;
                        Alpine.$data(
                            document.getElementById('delivery-address-id').querySelector('[x-data]')
                        ).asyncData.params.where[0][2] = id;
                        $wire.fetchContactData(true);
                    }
                }
                ">
                    <div class="space-y-2.5 divide-y divide-secondary-200">
                        <x-select
                            :options="$orderTypes"
                            option-label="name"
                            option-value="id"
                            :label="__('Order type')"
                            wire:model="replicateOrder.order_type_id"
                        />
                        <div class="pt-4">
                            <x-select
                                :label="__('Contact')"
                                class="pb-4"
                                wire:model="replicateOrder.contact_id"
                                option-value="contact_id"
                                option-label="label"
                                option-description="description"
                                :clearable="false"
                                x-on:selected="updateContactId($event.detail.contact_id)"
                                template="user-option"
                                :async-data="[
                                    'api' => route('search', \FluxErp\Models\Address::class),
                                    'method' => 'POST',
                                    'params' => [
                                        'option-value' => 'contact_id',
                                        'fields' => [
                                            'name',
                                            'contact_id',
                                            'firstname',
                                            'lastname',
                                            'company',
                                        ],
                                        'where' => [
                                            [
                                                'is_main_address',
                                                '=',
                                                true,
                                            ]
                                        ],
                                        'with' => 'contact.media',
                                    ]
                                ]"
                            />
                            <div id="invoice-address-id">
                                <x-select
                                    class="pb-4"
                                    :label="__('Invoice Address')"
                                    wire:model="replicateOrder.address_invoice_id"
                                    option-value="id"
                                    option-label="label"
                                    option-description="description"
                                    :clearable="false"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\Address::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'contact.media',
                                            'where' => [
                                                ['contact_id', '=', $order->contact_id],
                                            ],
                                        ]
                                    ]"
                                />
                            </div>
                            <div id="delivery-address-id">
                                <x-select
                                    :label="__('Delivery Address')"
                                    class="pb-4"
                                    wire:model="replicateOrder.address_delivery_id"
                                    option-value="id"
                                    option-label="label"
                                    option-description="description"
                                    :clearable="false"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\Address::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'contact.media',
                                            'where' => [
                                                ['contact_id', '=', $order->contact_id],
                                            ],
                                        ]
                                    ]"
                                />
                            </div>
                        </div>
                        <div class="space-y-3 pt-4">
                            <x-select
                                :label="__('Client')"
                                :options="$clients"
                                option-value="id"
                                option-label="name"
                                :clearable="false"
                                autocomplete="off"
                                wire:model="replicateOrder.client_id"
                            />
                            <x-select
                                :label="__('Price list')"
                                :options="$priceLists"
                                option-value="id"
                                option-label="name"
                                :clearable="false"
                                autocomplete="off"
                                wire:model="replicateOrder.price_list_id"
                            />
                            <x-select
                                :label="__('Payment method')"
                                :options="$paymentTypes"
                                option-value="id"
                                option-label="name"
                                :clearable="false"
                                autocomplete="off"
                                wire:model="replicateOrder.payment_type_id"
                            />
                            <x-select
                                :label="__('Language')"
                                :options="$languages"
                                option-value="id"
                                option-label="name"
                                :clearable="false"
                                autocomplete="off"
                                wire:model="replicateOrder.language_id"
                            />
                        </div>
                    </div>
                </section>
                <x-errors />
                <x-slot:footer>
                    <div class="flex justify-end gap-x-4">
                        <div class="flex">
                            <x-button flat :label="__('Cancel')" x-on:click="close" />
                            <x-button spinner="saveReplicate" primary :label="__('Save')" wire:click="saveReplicate()" />
                        </div>
                    </div>
                </x-slot:footer>
            </x-card>
        </x-modal>
    @show
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5">
        <div class="flex items-center space-x-5">
            <x-avatar xl :src="$order->contact['avatar_url'] ?? ''"></x-avatar>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex">
                        <x-heroicons x-show="$wire.order.is_locked" variant="solid" name="lock-closed" />
                        <x-heroicons x-show="! $wire.order.is_locked" variant="solid" name="lock-open" />
                        <div class="pl-2">
                            <span class="opacity-40 transition-opacity hover:opacity-100" x-text="$wire.order.order_type.name">
                            </span>
                            <span class="opacity-40 transition-opacity hover:opacity-100" x-text="$wire.order.invoice_number ? $wire.order.invoice_number : ($wire.order.order_number || $wire.order.id)">
                            </span>
                        </div>
                    </div>
                    <span x-text="$wire.order.address_invoice.description"></span>
                </h1>
            </div>
        </div>
        <div class="justify-stretch mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
            @if(\FluxErp\Actions\Order\ReplicateOrder::canPerformAction(false))
                <x-button
                    spinner="replicate"
                    class="w-full"
                    wire:click="replicate()"
                    :label="__('Replicate')"
                />
            @endif
            @if(\FluxErp\Actions\Order\DeleteOrder::canPerformAction(false) && ! $order->is_locked)
                <x-button
                    wire:confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Order')]) }}"
                    negative
                    label="{{ __('Delete') }}"
                    wire:click="delete"
                />
            @endif
            @if(\FluxErp\Actions\Order\UpdateOrder::canPerformAction(false) && ! $order->is_locked)
                <x-button
                    primary
                    spinner="save"
                    class="w-full"
                    x-on:click="$wire.save(orderPositions)"
                    :label="__('Save')"
                />
            @endif
        </div>
    </div>
    <x-tabs wire:loading="tab" wire:model="tab" :$tabs class="w-full lg:col-start-1 xl:col-span-2 xl:flex gap-4">
        <x-slot:prepend>
            <section class="relative basis-2/12" wire:ignore>
                <div class="sticky top-6 flex flex-col gap-4">
                    @section('contact-address-card')
                        <x-card>
                            <x-slot:header>
                                <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                                    <x-label>
                                        {{ __('Contact') }}
                                    </x-label>
                                    <div class="pl-2">
                                        <x-button outline icon="eye" href="{{ route('contacts.id?', $order->contact_id ?? '') }}">
                                        </x-button>
                                    </div>
                                </div>
                            </x-slot:header>
                            <div x-data="{
                                    updateContactId(id) {
                                        Alpine.$data(
                                            document.getElementById('order-invoice-address-id').querySelector('[x-data]')
                                        ).asyncData.params.where[0][2] = id;
                                        Alpine.$data(
                                            document.getElementById('order-delivery-address-id').querySelector('[x-data]')
                                        ).asyncData.params.where[0][2] = id;
                                        $wire.fetchContactData();
                                        console.log('a');
                                    }
                                }"
                            >
                                <x-select
                                    class="pb-4"
                                    :label="__('Contact')"
                                    :disabled="$order->is_locked"
                                    wire:model="order.contact_id"
                                    option-value="contact_id"
                                    option-label="label"
                                    option-description="description"
                                    :clearable="false"
                                    x-on:selected="updateContactId($event.detail.contact_id)"
                                    template="user-option"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\Address::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'option-value' => 'contact_id',
                                            'fields' => [
                                                'name',
                                                'contact_id',
                                                'firstname',
                                                'lastname',
                                                'company',
                                            ],
                                            'where' => [
                                                [
                                                    'is_main_address',
                                                    '=',
                                                    true,
                                                ]
                                            ],
                                            'with' => 'contact.media',
                                        ]
                                    ]"
                                />
                            </div>
                        </x-card>
                    @show
                    @section('invoice-address-card')
                        <x-card>
                            <x-slot:header>
                                <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                                    <x-label>
                                        {{ __('Invoice Address') }}
                                    </x-label>
                                    <div class="pl-2">
                                        <x-button outline icon="eye" href="{{ route('contacts.id?', $order->address_invoice['contact_id'] ?? '') }}">
                                        </x-button>
                                    </div>
                                </div>
                            </x-slot:header>
                            <div id="order-invoice-address-id">
                                <x-select
                                    :disabled="$order->is_locked"
                                    class="pb-4"
                                    wire:model.live="order.address_invoice_id"
                                    option-value="id"
                                    option-label="label"
                                    option-description="description"
                                    :clearable="false"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\Address::class),
                                        'params' => [
                                            'with' => 'contact.media',
                                            'where' => [
                                                ['contact_id', '=', $order->contact_id],
                                            ],
                                        ]
                                    ]"
                                />
                            </div>
                            <div class="text-sm">
                                <div x-text="$wire.order.address_invoice.company">
                                </div>
                                <div x-text="($wire.order.address_invoice.firstname + ' ' + $wire.order.address_invoice.lastname).trim()">
                                </div>
                                <div x-text="$wire.order.address_invoice.street">
                                </div>
                                <div x-text="($wire.order.address_invoice.zip + ' ' + $wire.order.address_invoice.city).trim()">
                                </div>
                            </div>
                        </x-card>
                    @show
                    @section('delivery-address-card')
                        <x-card>
                            <x-slot:header>
                                <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                                    <x-label>
                                        {{ __('Delivery Address') }}
                                    </x-label>
                                    <div class="pl-2">
                                        <x-button outline icon="eye" href="{{ route('contacts.id?', $order->address_delivery['contact_id'] ?? '') }}">
                                        </x-button>
                                    </div>
                                </div>
                            </x-slot:header>
                            <div id="order-delivery-address-id">
                                <x-select
                                    :disabled="$order->is_locked"
                                    class="pb-4"
                                    wire:model.live="order.address_delivery_id"
                                    option-value="id"
                                    option-label="label"
                                    option-description="description"
                                    :clearable="false"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\Address::class),
                                        'params' => [
                                            'with' => 'contact.media',
                                            'where' => [
                                                ['contact_id', '=', $order->contact_id],
                                            ],
                                        ]
                                    ]"
                                />
                            </div>
                            <div class="text-sm" x-bind:class="$wire.order.address_delivery_id === $wire.order.address_invoice_id && 'hidden'">
                                <div x-text="$wire.order.address_delivery?.company">
                                </div>
                                <div x-text="(($wire.order.address_delivery?.firstname ?? '') + ' ' + ($wire.order.address_delivery?.lastname ?? '')).trim()">
                                </div>
                                <div x-text="$wire.order.address_delivery?.street">
                                </div>
                                <div x-text="(($wire.order.address_delivery?.zip ?? '') + ' ' + ($wire.order.address_delivery?.city ?? '')).trim()">
                                </div>
                            </div>
                        </x-card>
                    @show
                    @section('general-card')
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
                                    wire:model.live="order.client_id"
                                />
                                <x-select
                                    :label="__('Commission Agent')"
                                    option-value="id"
                                    option-label="label"
                                    :disabled="$order->is_locked"
                                    autocomplete="off"
                                    wire:model="order.agent_id"
                                    :template="[
                                        'name'   => 'user-option',
                                    ]"
                                    :async-data="[
                                        'api' => route('search', \FluxErp\Models\User::class),
                                        'method' => 'POST',
                                        'params' => [
                                            'with' => 'media',
                                        ]
                                    ]"
                                />
                                <x-select
                                    :label="__('Price list')"
                                    :options="$priceLists"
                                    option-value="id"
                                    option-label="name"
                                    :clearable="false"
                                    autocomplete="off"
                                    wire:model.live="order.price_list_id"
                                    x-bind:disabled="$wire.order.is_locked"
                                />
                                <x-select
                                    :label="__('Payment method')"
                                    :options="$paymentTypes"
                                    option-value="id"
                                    option-label="name"
                                    :clearable="false"
                                    autocomplete="off"
                                    wire:model.live="order.payment_type_id"
                                    x-bind:disabled="$wire.order.is_locked"
                                />
                                <x-select
                                    :label="__('Language')"
                                    :options="$languages"
                                    option-value="id"
                                    option-label="name"
                                    :clearable="false"
                                    autocomplete="off"
                                    wire:model="order.language_id"
                                    x-bind:disabled="$wire.order.is_locked"
                                />
                            </div>
                        </x-card>
                    @show
                    @section('state-card')
                        <x-card>
                            <div class="space-y-3">
                                <x-state
                                    class="w-full"
                                    align="left"
                                    :label="__('Order state')"
                                    wire:model.live="order.state"
                                    formatters="formatter.state"
                                    available="availableStates.state"
                                />
                                <x-state
                                    align="left"
                                    :label="__('Payment state')"
                                    wire:model.live="order.payment_state"
                                    formatters="formatter.payment_state"
                                    available="availableStates.payment_state"
                                />
                                <x-state
                                    align="left"
                                    :label="__('Delivery state')"
                                    wire:model.live="order.delivery_state"
                                    formatters="formatter.delivery_state"
                                    available="availableStates.delivery_state"
                                />
                            </div>
                        </x-card>
                    @show
                </div>
            </section>
        </x-slot:prepend>
        @includeWhen($tab === 'order.order-positions', 'flux::livewire.order.order-positions')
        <x-slot:append>
            <section class="relative basis-2/12" wire:ignore>
                <div class="sticky top-6 space-y-6">
                    @section('content.right')
                        <x-card>
                            <div class="space-y-4">
                                @section('actions')
                                    @if($printLayouts)
                                        <x-button
                                            primary
                                            class="w-full"
                                            icon="document-text"
                                            x-on:click="$openModal('create-documents')"
                                            :label="__('Create Documents')"
                                        />
                                        <div class="dropdown-full-w">
                                            <x-dropdown width="w-full">
                                                <x-slot name="trigger">
                                                    <x-button class="w-full" icon="search">
                                                        {{ __('Preview') }}
                                                    </x-button>
                                                </x-slot>
                                                @foreach($printLayouts as $printLayout)
                                                    <x-dropdown.item
                                                        x-on:click="const previewNode = document.getElementById('preview'); document.getElementById('preview-iframe').src = '{{ route('print.render', ['model_id' => $order->id, 'view' => $printLayout, 'model_type' => \FluxErp\Models\Order::class, '']) }}'; $openModal(previewNode); preview = '{{ $printLayout }}';">
                                                        {{ __($printLayout) }}
                                                    </x-dropdown.item>
                                                @endforeach
                                            </x-dropdown>
                                        </div>
                                    @endif
                                @show
                            </div>
                        </x-card>
                        <x-card>
                            <div class="text-sm">
                                <div class="flex justify-between py-2.5">
                                    <div>
                                        {{ __('Margin') }}
                                    </div>
                                    <div>
                                        <span x-html="formatters.coloredMoney($wire.order.margin ?? 0)">
                                        </span>
                                    </div>
                                </div>
                                <div x-cloak x-show="$wire.order.total_net_price !== $wire.order.total_base_net_price">
                                    <div class="flex justify-between py-2.5">
                                        <div>
                                            {{ __('Sum net without discount') }}
                                        </div>
                                        <div>
                                            <span x-html="formatters.coloredMoney($wire.order.total_base_net_price ?? 0)">
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between py-2.5">
                                        <div>
                                            {{ __('Discount') }}
                                        </div>
                                        <div>
                                            <span x-html="formatters.coloredMoney(($wire.order.total_net_price - $wire.order.total_base_net_price) ?? 0)">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-between py-2.5">
                                    <div>
                                        {{ __('Sum net') }}
                                    </div>
                                    <div>
                                        <span x-html="formatters.coloredMoney($wire.order.total_net_price ?? 0)">
                                        </span>
                                    </div>
                                </div>
                                <hr />
                                <template x-for="vat in $wire.order.total_vats">
                                    <div class="flex justify-between py-2.5">
                                        <div>
                                            <span>{{ __('Plus ') }}</span>
                                            <span x-html="formatters.percentage(vat.vat_rate_percentage ?? 0)">
                                            </span>
                                        </div>
                                        <div>
                                            <span x-html="formatters.coloredMoney(vat.total_vat_price ?? 0)">
                                            </span>
                                        </div>
                                    </div>
                                </template>
                                <div class="dark:bg-secondary-700 flex justify-between bg-gray-50 py-2.5">
                                    <div>
                                        {{ __('Total Gross') }}
                                    </div>
                                    <div>
                                        <span x-html="formatters.coloredMoney($wire.order.total_gross_price ?? 0)">
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </x-card>
                        <x-card>
                            <div class="space-y-3">
                                @section('content.right.order_dates')
                                    <x-datetime-picker wire:model="order.invoice_date" :without-time="true" :disabled="true" :label="__('Invoice Date')" />
                                    <x-datetime-picker wire:model="order.system_delivery_date" :without-time="true" :disabled="$order->is_locked" :label="__('Performance/Delivery date')" />
                                    <x-datetime-picker wire:model="order.system_delivery_date_end" :without-time="true" :disabled="$order->is_locked" :label="__('Performance/Delivery date end')" />
                                    <x-datetime-picker wire:model="order.order_date" :without-time="true" :disabled="$order->is_locked" :label="__('Order Date')" />
                                    <x-input wire:model="order.commission" :disabled="$order->is_locked" :label="__('Commission')" />
                                @show
                            </div>
                        </x-card>
                    @show
                    <x-card>
                        <div class="grid grid-cols-3 auto-cols-max gap-1">
                            <span class="">{{ __('Created At') }}:</span>
                            <span x-text="window.formatters.datetime($wire.order.created_at)"></span>
                            <span x-text="$wire.order.created_by?.name"></span>
                            <span class="">{{ __('Updated At') }}:</span>
                            <span x-text="window.formatters.datetime($wire.order.updated_at)"></span>
                            <span x-text="$wire.order.updated_by?.name"></span>
                        </div>
                    </x-card>
                </div>
            </section>
        </x-slot:append>
    </x-tabs>
</div>
