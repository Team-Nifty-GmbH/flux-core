<x-flux::map.fullscreen-container>
    <x-slot:controls>
        @if ($this->getOrdersWithoutCoordinatesCount() > 0)
            <x-badge
                color="amber"
                :text="__(':count orders without coordinates', ['count' => $this->getOrdersWithoutCoordinatesCount()])"
            />
        @endif
    </x-slot>
</x-flux::map.fullscreen-container>
<div
    x-data="{
        updateContactId(id) {
            $tallstackuiSelect('invoice-address-id').mergeRequestParams({
                where: [['contact_id', '=', id]],
            })
            $tallstackuiSelect('delivery-address-id').mergeRequestParams({
                where: [['contact_id', '=', id]],
            })

            $wire.fetchContactData()
        },
    }"
>
    <x-modal id="create-order-modal" :title="__('New Order')">
        <section>
            <div class="space-y-2.5 divide-y divide-secondary-200">
                @if (! $orderType ?? true)
                    <x-select.styled
                        :label="__('Order type')"
                        required
                        wire:model="order.order_type_id"
                        select="label:name|value:id"
                        :options="$orderTypes"
                    />
                @endif

                <div class="flex flex-col gap-4 pt-4">
                    <x-select.styled
                        :label="__('Contact')"
                        class="pb-4"
                        wire:model="order.contact_id"
                        required
                        x-on:select="updateContactId($event.detail.select.contact_id)"
                        select="label:label|value:contact_id"
                        unfiltered
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
                                'with' => ['contact.media', 'country:id,name'],
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
                            unfiltered
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
                            unfiltered
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
                <div class="flex flex-col gap-4 pt-4">
                    <x-select.styled
                        :label="__('Tenant')"
                        required
                        autocomplete="off"
                        wire:model="order.tenant_id"
                        select="label:name|value:id"
                        :options="$tenants"
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
        <x-errors />
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('create-order-modal')"
            />
            <x-button
                loading="save"
                color="indigo"
                :text="__('Save')"
                wire:click="save"
            />
        </x-slot>
    </x-modal>

    <x-modal id="create-collective-order-modal" :title="__('Collective Order')">
        <div class="flex flex-col gap-4">
            <x-select.styled
                :label="__('Collective Order Type')"
                required
                wire:model="collectiveOrder.order_type_id"
                select="label:name|value:id"
                :options="resolve_static(\FluxErp\Models\OrderType::class, 'query')
                    ->where('order_type_enum', \FluxErp\Enums\OrderTypeEnum::CollectiveOrder->value)
                    ->where('is_active', true)
                    ->get(['id', 'name'])
                    ->toArray()
                "
            />
            <x-select.styled
                :label="__('Split Order Order Type')"
                required
                wire:model="collectiveOrder.split_order_order_type_id"
                select="label:name|value:id"
                :options="resolve_static(\FluxErp\Models\OrderType::class, 'query')
                    ->where('order_type_enum', \FluxErp\Enums\OrderTypeEnum::SplitOrder->value)
                    ->where('is_active', true)
                    ->get(['id', 'name'])
                    ->toArray()
                "
            />
            <x-card class="text-center">
                <div
                    class="text-2xl font-bold"
                    x-text="Object.values($wire.collectiveOrder.orders).length"
                ></div>
                <div class="text-sm">
                    {{ __('Collective Order(s) will be created') }}
                </div>
            </x-card>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('create-collective-order-modal')"
            />
            <x-button
                loading="createCollectiveOrders"
                color="indigo"
                :text="__('Create')"
                wire:click="createCollectiveOrders"
            />
        </x-slot>
    </x-modal>
    {{ $this->renderCreateDocumentsModal() }}
</div>
