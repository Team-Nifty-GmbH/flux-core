<div wire:ignore>
    <x-modal max-width="6xl" name="edit-order-position" x-on:close="$wire.resetOrderPosition()">
        <x-card>
            <div class="relative">
                <x-spinner  wire:target="position"/>
                <div class="space-y-2 p-4" colspan="100%">
                    <div class="flex w-full justify-items-stretch gap-3">
                        <div class="flex-auto space-y-2">
                            <x-checkbox wire:model.boolean="orderPosition.is_free_text" :label="__('Comment / Block')" />
                            <x-input :label="__('Name')" wire:model="orderPosition.name"/>
                            <div x-cloak x-show="$wire.orderPosition.is_free_text !== true">
                                <x-select
                                        x-on:selected="$wire.changedProductId($event.detail.id)"
                                        class="pb-4"
                                        :label="__('Product')"
                                        wire:model="orderPosition.product_id"
                                        option-value="id"
                                        option-label="label"
                                        option-description="product_number"
                                        :clearable="false"
                                        :template="[
                                            'name' => 'user-option',
                                        ]"
                                        :async-data="[
                                            'api' => route('search', \FluxErp\Models\Product::class),
                                            'params' => [
                                                'whereDoesntHave' => 'children',
                                                'fields' => ['id', 'name', 'product_number'],
                                                'with' => 'media',
                                            ]
                                        ]"
                                />
                                <div x-cloak x-show="$wire.orderPosition.product_id">
                                    <x-select
                                            :label="__('Warehouse')"
                                            wire:model="orderPosition.warehouse_id"
                                            option-value="id"
                                            option-label="name"
                                            :async-data="[
                                                'api' => route('search', \FluxErp\Models\Warehouse::class)
                                            ]"
                                    />
                                </div>
                                <div class="mt-2">
                                    <x-checkbox wire:model.boolean="orderPosition.is_alternative" :label="__('Alternative')" />
                                </div>
                            </div>
                        </div>
                        <div class="flex-auto space-y-2" x-cloak x-show="$wire.orderPosition.is_free_text !== true">
                            <x-input type="number" min="0" :label="__('Amount')" wire:model="orderPosition.amount" x-ref="amount" />
                            <x-input
                                    :prefix="$order->currency['symbol']"
                                    type="number"
                                    :label="__('Unit price :type', ['type' => ($orderPosition->is_net ?? true) ? __('net') : __('gross')])"
                                    wire:model="orderPosition.unit_price"
                                    x-on:change="$el.value = parseNumber($el.value)"
                            />
                            <x-input
                                :prefix="$order->currency['symbol']"
                                type="number"
                                :label="__('Purchase Price')"
                                wire:model="orderPosition.purchase_price"
                                x-on:change="$el.value = parseNumber($el.value)"
                            />
                            <x-input type="number" :label="__('Discount')" wire:model="orderPosition.discount_percentage" />
                            <x-select
                                :options="$vatRates"
                                option-value="id"
                                option-label="name"
                                :label="__('Vat rate')"
                                wire:model.live="orderPosition.vat_rate_id"
                            />
                            <x-select
                                :label="__('Ledger Account')"
                                option-value="id"
                                option-label="name"
                                option-description="number"
                                wire:model.number="orderPosition.ledger_account_id"
                                :async-data="[
                                    'api' => route('search', \FluxErp\Models\LedgerAccount::class),
                                    'params' => [
                                        'where' => [
                                            [
                                                'ledger_account_type_enum',
                                                '=',
                                                $order->isPurchase
                                                    ? \FluxErp\Enums\LedgerAccountTypeEnum::Expense
                                                    : \FluxErp\Enums\LedgerAccountTypeEnum::Revenue,
                                            ],
                                        ]
                                    ]
                                ]"
                            />
                        </div>
                    </div>
                    <x-flux::editor :label="__('Description')" wire:model="orderPosition.description" />
                </div>
                <x-errors />
            </div>
            <x-slot:footer>
                <div class="flex justify-between gap-x-4">
                    <div x-show="$wire.orderPosition.id">
                        <x-button flat negative :label="__('Delete')" x-on:click="$wire.deleteOrderPosition(); close();" />
                    </div>
                    <div class="flex w-full justify-end">
                        <x-button flat :label="__('Cancel')" x-on:click="close" />
                        <x-button
                                primary
                                wire:click="addOrderPosition().then((success) => {if(success) close();})"
                                x-show="!$wire.order.is_locked"
                                x-on:click=""
                                :label="__('Save')"
                        />
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <div class="w-full xl:space-x-6">
        <div class="ml:p-10 relative min-h-full space-y-6">
            <div>
                @include('tall-datatables::livewire.data-table')
                <div x-show="! $wire.order.is_locked" x-cloak class="sticky bottom-6 pt-6">
                    <x-card class="flex gap-4">
                        <div class="flex gap-4 max-w-md w-full">
                            <x-select
                                class="pb-4"
                                :label="__('Product')"
                                wire:model="orderPosition.product_id"
                                option-value="id"
                                option-label="label"
                                option-description="product_number"
                                :clearable="false"
                                :template="[
                                    'name'   => 'user-option',
                                ]"
                                :async-data="[
                                    'api' => route('search', \FluxErp\Models\Product::class),
                                    'params' => [
                                        'whereDoesntHave' => 'children',
                                        'fields' => ['id', 'name', 'product_number'],
                                        'with' => 'media',
                                    ]
                                ]"
                            />
                            <div
                                x-transition
                                x-cloak
                                x-ref="quickAddAmount"
                                x-show="$wire.orderPosition.product_id"
                            >
                                <x-inputs.number
                                    :label="__('Amount')"
                                    wire:model="orderPosition.amount"
                                    wire:keyup.enter="quickAdd()"
                                />
                            </div>
                        </div>
                        <div class="flex gap-1.5 items-center pt-2">
                            <div x-transition x-cloak x-show="$wire.orderPosition.product_id">
                                <x-button
                                    positive
                                    icon="plus"
                                    :label="__('Quick add')"
                                    wire:click="quickAdd()"
                                />
                            </div>
                            <div>
                                <x-button
                                        :label="__('Add Detailed')"
                                        primary
                                        icon="pencil"
                                        x-ref="addPosition"
                                        wire:click="editOrderPosition().then(() => $openModal('edit-order-position'))"
                                />
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </div>
</div>
