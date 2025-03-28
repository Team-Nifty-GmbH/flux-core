<div wire:ignore>
    <x-modal
        id="edit-position-discount"
        x-on:open="$focus.first()"
        x-on:close="$wire.discount = null"
        x-on:keyup.enter="$wire.discountSelectedPositions().then(() => {close();})"
        x-trap="show"
        persistent
    >
        <div class="flex flex-col gap-4">
            <x-input
                prefix="%"
                type="number"
                x-on:focus=""
                :label="__('Discount')"
                wire:model="discount"
                x-on:change="$el.value = parseNumber($el.value)"
            />
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                :text="__('Cancel')"
                x-on:click="$modalClose('edit-position-discount')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                wire:click="discountSelectedPositions().then(() => {$modalClose('edit-position-discount');})"
            />
        </x-slot>
    </x-modal>
    <x-modal
        size="6xl"
        id="edit-order-position"
        x-on:close="$wire.resetOrderPosition()"
        persistent
    >
        @section('order-position-detail-modal.content')
        <div class="relative">
            <x-flux::spinner wire:target="position" />
            <div class="space-y-2 p-4" colspan="100%">
                <div class="flex w-full justify-items-stretch gap-3">
                    <div class="flex-auto space-y-2">
                        @section('order-position-detail-modal.content.left')
                        <x-checkbox
                            wire:model.boolean="orderPosition.is_free_text"
                            :label="__('Comment / Block')"
                        />
                        <x-input
                            :label="__('Name')"
                            wire:model="orderPosition.name"
                        />
                        <div
                            x-cloak
                            x-show="$wire.orderPosition.is_free_text !== true"
                        >
                            <x-select.styled
                                x-on:select="$wire.changedProductId($event.detail.select.id)"
                                class="pb-4"
                                :label="__('Product')"
                                wire:model="orderPosition.product_id"
                                required
                                select="label:label|value:id|description:product_number"
                                :request="[
                                    'url' => route('search', \FluxErp\Models\Product::class),
                                    'method' => 'POST',
                                    'params' => [
                                        'whereDoesntHave' => 'children',
                                        'fields' => [
                                            'id',
                                            'name',
                                            'product_number',
                                        ],
                                        'with' => 'media',
                                    ],
                                ]"
                            />
                            <div
                                x-cloak
                                x-show="$wire.orderPosition.product_id"
                            >
                                <x-select.styled
                                    :label="__('Warehouse')"
                                    wire:model="orderPosition.warehouse_id"
                                    select="label:name|value:id"
                                    :request="[
                                        'url' => route('search', \FluxErp\Models\Warehouse::class),
                                        'method' => 'POST',
                                    ]"
                                />
                            </div>
                            <div class="mt-2">
                                <x-checkbox
                                    wire:model.boolean="orderPosition.is_alternative"
                                    :label="__('Alternative')"
                                />
                            </div>
                        </div>
                        @show
                    </div>
                    <div
                        class="flex-auto space-y-2"
                        x-cloak
                        x-show="$wire.orderPosition.is_free_text !== true"
                    >
                        @section('order-position-detail-modal.content.right')
                        <x-input
                            type="number"
                            min="0"
                            :label="__('Amount')"
                            wire:model="orderPosition.amount"
                            x-ref="amount"
                        />
                        <x-input
                            :prefix="data_get($order->currency, 'symbol')"
                            type="number"
                            :label="__('Unit price :type', ['type' => ($orderPosition->is_net ?? true) ? __('net') : __('gross')])"
                            wire:model="orderPosition.unit_price"
                            x-on:change="$el.value = parseNumber($el.value)"
                        />
                        <x-input
                            :prefix="data_get($order->currency, 'symbol')"
                            type="number"
                            :label="__('Purchase Price')"
                            wire:model="orderPosition.purchase_price"
                            x-on:change="$el.value = parseNumber($el.value)"
                        />
                        <x-input
                            prefix="%"
                            type="number"
                            :label="__('Discount')"
                            wire:model="orderPosition.discount_percentage"
                            x-on:change="$el.value = parseNumber($el.value)"
                        />
                        <x-select.styled
                            :options="$vatRates"
                            select="label:name|value:id"
                            :label="__('Vat rate')"
                            wire:model.live="orderPosition.vat_rate_id"
                        />
                        <x-select.styled
                            :label="__('Ledger Account')"
                            select="label:name|value:id"
                            wire:model.number="orderPosition.ledger_account_id"
                            select="label:name|value:id|description:number"
                            :request="[
                                'url' => route('search', \FluxErp\Models\LedgerAccount::class),
                                'method' => 'POST',
                                'params' => [
                                    'where' => [
                                        [
                                            'ledger_account_type_enum',
                                            '=',
                                            $order->isPurchase
                                                ? \FluxErp\Enums\LedgerAccountTypeEnum::Expense
                                                : \FluxErp\Enums\LedgerAccountTypeEnum::Revenue,
                                        ],
                                    ],
                                ],
                            ]"
                        />
                        @show
                    </div>
                </div>
                @section('order-position-detail-modal.content.bottom')
                <x-flux::editor
                    :label="__('Description')"
                    wire:model="orderPosition.description"
                />
                @show
            </div>
            <x-errors />
        </div>
        @show
        <x-slot:footer>
            <div class="flex justify-between gap-x-4">
                <div x-show="$wire.orderPosition.id">
                    <x-button
                        flat
                        color="red"
                        :text="__('Delete')"
                        wire:click="deleteOrderPosition().then((success) => {if(success) $modalClose('edit-order-position');})"
                        wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Order Position')]) }}"
                    />
                </div>
                <div class="flex w-full justify-end gap-x-2">
                    <x-button
                        color="secondary"
                        light
                        flat
                        :text="__('Cancel')"
                        x-on:click="$modalClose('edit-order-position')"
                    />
                    <x-button
                        color="indigo"
                        wire:click="addOrderPosition().then((success) => {if(success) $modalClose('edit-order-position');})"
                        x-show="!$wire.order.is_locked"
                        x-on:click=""
                        :text="__('Save')"
                    />
                </div>
            </div>
        </x-slot>
    </x-modal>
</div>
