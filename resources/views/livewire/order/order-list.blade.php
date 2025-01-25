<div x-data="{
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
                    @if(! $orderType ?? true)
                        <x-select
                            :options="$orderTypes"
                            option-label="name"
                            option-value="id"
                            :label="__('Order type')"
                            wire:model="order.order_type_id"
                        />
                    @endif
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
                                    'with' => ['contact.media', 'country:id,name'],
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
    {{ $this->renderCreateDocumentsModal() }}
</div>
