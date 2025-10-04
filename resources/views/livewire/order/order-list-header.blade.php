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
        x-on:open="$focusOn('order-position-name')"
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
                            id="order-position-name"
                            :label="__('Name')"
                            wire:model="orderPosition.name"
                        />
                        <div
                            class="space-y-2"
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
                                unfiltered
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
                                    unfiltered
                                    :request="[
                                        'url' => route('search', \FluxErp\Models\Warehouse::class),
                                        'method' => 'POST',
                                    ]"
                                />
                            </div>
                            <div>
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
                            wire:model="orderPosition.unit_price"
                            x-on:change="$el.value = parseNumber($el.value)"
                        >
                            <x-slot:label>
                                <x-label for="orderPosition.unit_price">
                                    <span
                                        x-text="
                                            $wire.orderPosition.is_net
                                                ? '{{ __('Unit price :type', ['type' => __('net')]) }}'
                                                : '{{ __('Unit price :type', ['type' => __('gross')]) }}'
                                        "
                                    ></span>
                                </x-label>
                            </x-slot>
                        </x-input>
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
                            :label="__('Vat rate')"
                            wire:model.live="orderPosition.vat_rate_id"
                            select="label:name|value:id"
                            :options="$vatRates"
                        />
                        <x-select.styled
                            :label="__('Ledger Account')"
                            wire:model.number="orderPosition.ledger_account_id"
                            select="label:name|value:id|description:number"
                            unfiltered
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
                        <x-select.styled
                            :label="__('Credit Account')"
                            wire:model.number="orderPosition.credit_account_id"
                            select="label:label|value:id|description:description"
                            unfiltered
                            :request="[
                                'url' => route('search', \FluxErp\Models\ContactBankConnection::class),
                                'method' => 'POST',
                                'params' => [
                                    'where' => [
                                        [
                                            'contact_id',
                                            '=',
                                            $order->contact_id,
                                        ],
                                        [
                                            'is_credit_account',
                                            true,
                                        ],
                                    ],
                                ],
                            ]"
                        />
                        <div
                            class="space-y-2"
                            x-cloak
                            x-show="$wire.orderPosition.credit_account_id"
                        >
                            <div class="flex justify-between">
                                <div>
                                    <x-radio
                                        id="credit-on-credit-account-radio"
                                        name="post-on-credit-account-radio"
                                        :label="__('Credit')"
                                        wire:model="orderPosition.post_on_credit_account"
                                        :value="\FluxErp\Enums\CreditAccountPostingEnum::Credit->value"
                                    />
                                </div>
                                <div>
                                    <x-radio
                                        id="debit-on-credit-account-radio"
                                        name="post-on-credit-account-radio"
                                        :label="__('Debit')"
                                        wire:model="orderPosition.post_on_credit_account"
                                        :value="\FluxErp\Enums\CreditAccountPostingEnum::Debit->value"
                                    />
                                </div>
                            </div>
                            <x-input
                                type="number"
                                :label="__('Credit Amount')"
                                wire:model="orderPosition.credit_amount"
                            />
                        </div>
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
