<div x-data="{order: $wire.entangle('order').defer}" x-on:create-order="$openModal(document.getElementById('create'))">
    <x-modal.card id="create" :title="__('New Order')">
        <section>
            <div class="space-y-2.5">
                <x-select
                    :options="$orderTypes"
                    option-label="name"
                    option-value="id"
                    :label="__('Order type')"
                    wire:model="order.order_type_id"
                />
                <x-select
                    :label="__('Contact')"
                    class="pb-4"
                    wire:model="order.contact_id"
                    option-value="contact_id"
                    option-label="label"
                    option-description="description"
                    :clearable="false"
                    :async-data="[
                        'api' => route('search', \FluxErp\Models\Address::class),
                        'params' => [
                            'fields' => [
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
                @if($order['contact_id'])
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
                            'params' => [
                                'with' => 'contact.media',
                                'where' => [
                                    ['contact_id', '=', $order['contact_id']],
                                ],
                            ]
                        ]"
                    />
                    <div class="text-sm">
                        <div x-text="order.address_invoice?.company">
                        </div>
                        <div x-text="(order.address_invoice?.firstname + ' ' + order.address_invoice.lastname).trim()">
                        </div>
                        <div x-text="order.address_invoice?.street">
                        </div>
                        <div x-text="(order.address_invoice?.zip + ' ' + order.address_invoice?.city).trim()">
                        </div>
                    </div>
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
                @endif
                <x-card>
                    <div class="space-y-3">
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
    </x-modal.card>
    <div wire:ignore>
        <livewire:data-tables.order-list />
    </div>
</div>
