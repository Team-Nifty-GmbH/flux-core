<div>
    <x-modal id="create-order-modal" :title="__('New Order')">
        <section>
            <div class="space-y-2.5 divide-y divide-secondary-200">
                <x-select.styled
                    :options="$orderTypes"
                    select="label:name|value:id"
                    :label="__('Order type')"
                    wire:model="order.order_type_id"
                />
                <div class="pt-4">
                    <x-select.styled
                        :label="__('Contact')"
                        class="pb-4"
                        wire:model="order.contact_id"
                        select="label:label|value:contact_id"
                        option-description="description"
                        required
                        disabled
                        x-on:select="updateContactId($event.detail.select.value)"
                        :request="[
                            'url' => route('search', \FluxErp\Models\Address::class),
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
                                    ],
                                ],
                                'with' => 'contact.media',
                            ],
                        ]"
                    />
                    <div id="invoice-address-id">
                        <x-select.styled
                            class="pb-4"
                            :label="__('Invoice Address')"
                            wire:model="order.address_invoice_id"
                            option-description="description"
                            required
                            :request="[
                                'url' => route('search', \FluxErp\Models\Address::class),
                                'method' => 'POST',
                                'params' => [
                                    'with' => 'contact.media',
                                    'where' => [
                                        [
                                            'contact_id',
                                            '=',
                                            $order->contact_id,
                                        ],
                                    ],
                                ],
                            ]"
                        />
                    </div>
                    <div id="delivery-address-id">
                        <x-select.styled
                            :label="__('Delivery Address')"
                            class="pb-4"
                            wire:model="order.address_delivery_id"
                            option-description="description"
                            required
                            :request="[
                                'url' => route('search', \FluxErp\Models\Address::class),
                                'method' => 'POST',
                                'params' => [
                                    'with' => 'contact.media',
                                    'where' => [
                                        [
                                            'contact_id',
                                            '=',
                                            $order->contact_id,
                                        ],
                                    ],
                                ],
                            ]"
                        />
                    </div>
                </div>
                <div class="space-y-3 pt-4">
                    <x-select.styled
                        :label="__('Client')"
                        :options="$clients"
                        select="label:name|value:id"
                        required
                        autocomplete="off"
                        wire:model="order.client_id"
                    />
                    <x-select.styled
                        :label="__('Price list')"
                        :options="$priceLists"
                        select="label:name|value:id"
                        required
                        autocomplete="off"
                        wire:model="order.price_list_id"
                    />
                    <x-select.styled
                        :label="__('Payment method')"
                        :options="$paymentTypes"
                        select="label:name|value:id"
                        required
                        autocomplete="off"
                        wire:model="order.payment_type_id"
                    />
                    <x-select.styled
                        :label="__('Language')"
                        :options="$languages"
                        select="label:name|value:id"
                        required
                        autocomplete="off"
                        wire:model="order.language_id"
                    />
                </div>
            </div>
        </section>
        <x-slot:footer>
            <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('create-order-modal')" />
            <x-button loading color="indigo" :text="__('Save')" wire:click="save" />
        </x-slot:footer>
    </x-modal>
</div>
