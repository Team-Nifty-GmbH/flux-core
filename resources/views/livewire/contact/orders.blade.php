<div>
    <x-modal id="create-order-modal" :title="__('New Order')">
        <section>
            <div class="divide-secondary-200 space-y-2.5 divide-y">
                <x-select.styled
                    :label="__('Order type')"
                    wire:model="order.order_type_id"
                    select="label:name|value:id"
                    :options="$orderTypes"
                />
                <div class="pt-4">
                    <x-select.styled
                        :label="__('Contact')"
                        class="pb-4"
                        wire:model="order.contact_id"
                        required
                        disabled
                        x-on:select="updateContactId($event.detail.select.value)"
                        select="label:label|value:contact_id"
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
                            required
                            select="label:label|value:id"
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
                            required
                            select="label:label|value:id"
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
                        required
                        autocomplete="off"
                        wire:model="order.client_id"
                        select="label:name|value:id"
                        :options="$clients"
                    />
                    <x-select.styled
                        :label="__('Price list')"
                        required
                        autocomplete="off"
                        wire:model="order.price_list_id"
                        select="label:name|value:id"
                        :options="$priceLists"
                    />
                    <x-select.styled
                        :label="__('Payment method')"
                        required
                        autocomplete="off"
                        wire:model="order.payment_type_id"
                        select="label:name|value:id"
                        :options="$paymentTypes"
                    />
                    <x-select.styled
                        :label="__('Language')"
                        required
                        autocomplete="off"
                        wire:model="order.language_id"
                        select="label:name|value:id"
                        :options="$languages"
                    />
                </div>
            </div>
        </section>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('create-order-modal')"
            />
            <x-button
                loading
                color="indigo"
                :text="__('Save')"
                wire:click="save"
            />
        </x-slot>
    </x-modal>
</div>
