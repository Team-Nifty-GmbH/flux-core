<div x-data="{
        order: $wire.entangle('order'),
        updateContactId(id) {
            const modal = document.querySelector('[wireui-modal]');
            Alpine.$data(
                document.getElementById('invoice-address-id').querySelector('[x-data]')
            ).asyncData.params.where[0][2] = id;
            Alpine.$data(
                document.getElementById('delivery-address-id').querySelector('[x-data]')
            ).asyncData.params.where[0][2] = id;
            $wire.fetchContactData();
        }
    }">
    <x-modal name="create-order">
        <x-card :title="__('New Order')">
            <section>
                <div class="space-y-2.5 divide-y divide-secondary-200">
                    <x-select
                        :options="$orderTypes"
                        option-label="name"
                        option-value="id"
                        :label="__('Order type')"
                        wire:model="order.order_type_id"
                    />
                    <div class="pt-4">
                        <x-select
                            :label="__('Contact')"
                            class="pb-4"
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
                        <div id="invoice-address-id">
                            <x-select
                                class="pb-4"
                                :label="__('Invoice Address')"
                                wire:model="order.address_invoice_id"
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
                                wire:model="order.address_delivery_id"
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
                        />
                        <x-select
                            :label="__('Payment method')"
                            :options="$paymentTypes"
                            option-value="id"
                            option-label="name"
                            :clearable="false"
                            autocomplete="off"
                            wire:model="order.payment_type_id"
                        />
                        <x-select
                            :label="__('Language')"
                            :options="$languages"
                            option-value="id"
                            option-label="name"
                            :clearable="false"
                            autocomplete="off"
                            wire:model="order.language_id"
                        />
                    </div>
                </div>
            </section>
            <x-errors />
            <x-slot name="footer">
                <div class="flex justify-end gap-x-4">
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close" />
                        <x-button spinner primary :label="__('Save')" wire:click="save" />
                    </div>
                </div>
            </x-slot>
        </x-card>
    </x-modal>
    <x-modal name="create-documents">
        <x-card :title="__('Create Documents')">
            <div class="grid grid-cols-4 gap-1.5">
                <div class="font-semibold text-sm">{{ __('Print') }}</div>
                <div class="font-semibold text-sm">{{ __('Email') }}</div>
                <div class="font-semibold text-sm">{{ __('Download') }}</div>
                <div class="font-semibold text-sm">{{ __('Force Create') }}</div>
                @foreach($printLayouts as $printLayout)
                    <div class="text-ellipsis overflow-hidden">
                        <x-checkbox wire:model.boolean="selectedPrintLayouts.print.{{ $printLayout }}" :label="__($printLayout)" />
                    </div>
                    <div class="text-ellipsis overflow-hidden">
                        <x-checkbox class="truncate" wire:model.boolean="selectedPrintLayouts.email.{{ $printLayout }}" :label="__($printLayout)" />
                    </div>
                    <div class="text-ellipsis overflow-hidden">
                        <x-checkbox class="truncate" wire:model.boolean="selectedPrintLayouts.download.{{ $printLayout }}" :label="__($printLayout)" />
                    </div>
                    <div class="text-ellipsis overflow-hidden">
                        <x-checkbox class="truncate" wire:model.boolean="selectedPrintLayouts.force.{{ $printLayout }}" :label="__($printLayout)" />
                    </div>
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
    <div wire:ignore>
        @include('tall-datatables::livewire.data-table')
    </div>
</div>
