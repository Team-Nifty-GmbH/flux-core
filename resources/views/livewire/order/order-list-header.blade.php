<div wire:ignore>
    <x-modal
        id="edit-position-discount"
        x-on:open="$focus.first()"
        x-on:close="$wire.discount = null; $wire.discountIsPercentage = true"
        x-on:keyup.enter="$wire.discountSelectedPositions().then(() => {close();})"
        x-trap="show"
        persistent
    >
        <div class="flex flex-col gap-4">
            <x-toggle
                wire:model.live="discountIsPercentage"
                :label="__('Percent')"
            />
            <div x-cloak x-show="$wire.discountIsPercentage">
                <x-input
                    prefix="%"
                    type="number"
                    x-on:focus=""
                    :label="__('Discount')"
                    wire:model="discount"
                    x-on:change="$el.value = parseNumber($el.value)"
                />
            </div>
            <div x-cloak x-show="! $wire.discountIsPercentage">
                <x-input
                    :prefix="data_get($order->currency, 'symbol')"
                    type="number"
                    x-on:focus=""
                    :label="__('Discount')"
                    wire:model="discount"
                    x-on:change="$el.value = parseNumber($el.value)"
                />
            </div>
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
            <div class="space-y-6 p-4">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-4">
                        @section('order-position-detail-modal.content.left')
                        <div class="flex items-center gap-4">
                            <x-checkbox
                                wire:model.boolean="orderPosition.is_free_text"
                                :label="__('Comment / Block')"
                            />
                            <div
                                x-cloak
                                x-show="$wire.orderPosition.is_free_text !== true"
                            >
                                <x-checkbox
                                    wire:model.boolean="orderPosition.is_alternative"
                                    :label="__('Alternative')"
                                />
                            </div>
                        </div>
                        <x-input
                            id="order-position-name"
                            :label="__('Name')"
                            wire:model="orderPosition.name"
                        />
                        <div
                            class="space-y-4"
                            x-cloak
                            x-show="$wire.orderPosition.is_free_text !== true"
                        >
                            <x-select.styled
                                x-on:select="$wire.changedProductId($event.detail.select.id)"
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
                            @if (resolve_static(\FluxErp\Models\Warehouse::class, 'query')->count() > 1)
                                <div
                                    x-cloak
                                    x-show="$wire.orderPosition.product_id"
                                >
                                    <x-select.styled
                                        :label="__('Warehouse')"
                                        wire:model="orderPosition.warehouse_id"
                                        select="label:name|value:id"
                                        unfiltered
                                        required
                                        :request="[
                                            'url' => route('search', \FluxErp\Models\Warehouse::class),
                                            'method' => 'POST',
                                        ]"
                                    />
                                </div>
                            @endif
                        </div>
                        @show
                    </div>

                    <div
                        x-cloak
                        x-show="$wire.orderPosition.is_free_text !== true"
                        x-data="{
                            showAdvanced: false,
                            get basePrice() {
                                return (
                                    (parseFloat($wire.orderPosition.amount) || 0) *
                                    (parseFloat($wire.orderPosition.unit_price) || 0)
                                )
                            },
                            get discountAmount() {
                                if ($wire.orderPosition.discount_is_percentage) {
                                    return (
                                        this.basePrice *
                                        ((parseFloat($wire.orderPosition.discount_percentage) || 0) /
                                            100)
                                    )
                                }
                                return parseFloat($wire.orderPosition.discount_flat) || 0
                            },
                            get totalPrice() {
                                return Math.max(0, this.basePrice - this.discountAmount)
                            },
                        }"
                        class="space-y-4"
                    >
                        @section('order-position-detail-modal.content.right')
                        <div class="grid grid-cols-2 gap-4">
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
                        </div>
                        <x-select.styled
                            :label="__('Vat rate')"
                            wire:model.live="orderPosition.vat_rate_id"
                            select="label:name|value:id"
                            :options="$vatRates"
                        />
                        <div
                            class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <span
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ __('Discount') }}
                                </span>
                                <x-toggle
                                    wire:model.live="orderPosition.discount_is_percentage"
                                    :label="__('Percent')"
                                    x-on:click="
                                        const wasPercentage = $wire.orderPosition.discount_is_percentage;
                                        if (wasPercentage) {
                                            if (!$wire.orderPosition.discount_flat) {
                                                const flat = basePrice * ((parseFloat($wire.orderPosition.discount_percentage) || 0) / 100);
                                                $wire.orderPosition.discount_flat = parseFloat(flat.toFixed(2)).toString();
                                            }
                                        } else {
                                            if (!$wire.orderPosition.discount_percentage) {
                                                const percentage = basePrice > 0 ? ((parseFloat($wire.orderPosition.discount_flat) || 0) / basePrice) * 100 : 0;
                                                $wire.orderPosition.discount_percentage = parseFloat(percentage.toFixed(9)).toString();
                                            }
                                        }
                                    "
                                />
                            </div>
                            <div
                                x-cloak
                                x-show="$wire.orderPosition.discount_is_percentage"
                            >
                                <x-input
                                    prefix="%"
                                    type="number"
                                    wire:model="orderPosition.discount_percentage"
                                    x-on:change="$el.value = parseNumber($el.value)"
                                />
                            </div>
                            <div
                                x-cloak
                                x-show="! $wire.orderPosition.discount_is_percentage"
                            >
                                <x-input
                                    :prefix="data_get($order->currency, 'symbol')"
                                    type="number"
                                    wire:model="orderPosition.discount_flat"
                                    x-on:change="$el.value = parseNumber($el.value)"
                                />
                            </div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    {{ __('Total') }}
                                </span>
                                <span
                                    class="text-lg font-semibold text-gray-900 dark:text-white"
                                    x-text="
                                        totalPrice.toLocaleString(document.documentElement.lang, {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        }) + ' {{ data_get($order->currency, 'symbol') }}'
                                    "
                                ></span>
                            </div>
                            <div
                                x-cloak
                                x-show="discountAmount > 0"
                                class="mt-1 text-right"
                            >
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    <span
                                        x-text="
                                            '(-' +
                                                discountAmount.toLocaleString(document.documentElement.lang, {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                }) +
                                                ' {{ data_get($order->currency, 'symbol') }})'
                                        "
                                    ></span>
                                </span>
                            </div>
                        </div>
                        <div
                            class="border-t border-gray-200 pt-2 dark:border-gray-700"
                        >
                            <button
                                type="button"
                                class="flex w-full items-center justify-between py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300"
                                x-on:click="showAdvanced = !showAdvanced"
                            >
                                <span>{{ __('Advanced') }}</span>
                                <x-icon
                                    name="chevron-down"
                                    class="h-4 w-4 transition-transform"
                                    x-bind:class="showAdvanced && 'rotate-180'"
                                />
                            </button>
                            <div x-cloak x-show="showAdvanced" x-collapse>
                                <div class="space-y-4 pt-2">
                                    <x-input
                                        :prefix="data_get($order->currency, 'symbol')"
                                        type="number"
                                        :label="__('Purchase Price')"
                                        wire:model="orderPosition.purchase_price"
                                        x-on:change="$el.value = parseNumber($el.value)"
                                    />
                                    @if (resolve_static(\FluxErp\Models\LedgerAccount::class, 'query')->where('ledger_account_type_enum', $order->isPurchase ? \FluxErp\Enums\LedgerAccountTypeEnum::Expense : \FluxErp\Enums\LedgerAccountTypeEnum::Revenue)->exists())
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
                                    @endif

                                    @if (resolve_static(\FluxErp\Models\ContactBankConnection::class, 'query')->where('contact_id', $order->contact_id)->where('is_credit_account', true)->exists())
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
                                            class="space-y-4"
                                            x-cloak
                                            x-show="$wire.orderPosition.credit_account_id"
                                        >
                                            <div class="flex gap-4">
                                                <x-radio
                                                    id="credit-on-credit-account-radio"
                                                    name="post-on-credit-account-radio"
                                                    :label="__('Credit')"
                                                    wire:model="orderPosition.post_on_credit_account"
                                                    :value="\FluxErp\Enums\CreditAccountPostingEnum::Credit->value"
                                                />
                                                <x-radio
                                                    id="debit-on-credit-account-radio"
                                                    name="post-on-credit-account-radio"
                                                    :label="__('Debit')"
                                                    wire:model="orderPosition.post_on_credit_account"
                                                    :value="\FluxErp\Enums\CreditAccountPostingEnum::Debit->value"
                                                />
                                            </div>
                                            <x-input
                                                type="number"
                                                :label="__('Credit Amount')"
                                                wire:model="orderPosition.credit_amount"
                                            />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @show
                    </div>
                </div>

                @section('order-position-detail-modal.content.bottom')
                <x-flux::editor
                    wire:model="orderPosition.description"
                    scope="orderPosition"
                    :label="__('Description')"
                    :blade-variables="\FluxErp\Facades\Editor::getTranslatedVariables(\FluxErp\Models\OrderPosition::class)"
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
