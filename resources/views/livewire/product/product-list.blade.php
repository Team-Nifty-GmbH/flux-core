<div x-data="{ edit: true }">
    @canAction(\FluxErp\Actions\Product\CreateProduct::class)
        <x-modal
            id="create-product-modal"
            :title="__('New Product')"
            x-on:open="$focusOn('product-name')"
        >
            <section class="flex flex-col gap-1.5">
                <x-input
                    wire:model="product.product_number"
                    :label="__('Product Number')"
                    :placeholder="__('Leave empty to generate a new :attribute.', ['attribute' => __('Product Number')])"
                />
                <div x-show="$wire.productTypes.length" x-cloak>
                    <x-select.styled
                        wire:model="product.product_type"
                        :label="__('Product Type')"
                        required
                        :options="$productTypes"
                    />
                </div>
                <x-input
                    wire:model="product.name"
                    :label="__('Name')"
                    id="product-name"
                />
                <x-select.styled
                    multiple
                    x-bind:disabled="!edit"
                    wire:model.number="product.clients"
                    :label="__('Clients')"
                    :src="'logo_small_url'"
                    select="label:name|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\Client::class),
                        'method' => 'POST',
                    ]"
                />
                <x-flux::editor
                    wire:model="product.description"
                    :label="__('Description')"
                />
            </section>
            <section class="flex flex-col gap-1.5">
                <x-flux::product.prices :product="$product" />
            </section>
            <x-slot:footer>
                <x-button
                    color="secondary"
                    light
                    flat
                    :text="__('Cancel')"
                    x-on:click="$modalClose('create-product-modal')"
                />
                <x-button
                    loading="save"
                    color="indigo"
                    :text="__('Save')"
                    wire:click="save"
                />
            </x-slot>
        </x-modal>
    @endcanAction

    @canAction(\FluxErp\Actions\Product\ProductPricesUpdate::class)
        <x-modal id="update-prices-modal" :title="__('Update prices')">
            <div class="flex flex-col gap-1.5">
                <x-select.styled
                    :label="__('Price List')"
                    wire:model="productPricesUpdate.price_list_id"
                    select="label:name|value:id"
                    :options="$selectablePriceLists"
                />
                <x-select.styled
                    :label="__('Use price from')"
                    wire:model="productPricesUpdate.base_price_list_id"
                    :clearable="true"
                    select="label:name|value:id"
                    :options="$selectablePriceLists"
                />
                <div class="mt-2">
                    <x-toggle
                        wire:model="productPricesUpdate.is_percent"
                        :label="__('Is Percentage')"
                    />
                </div>
                <x-number wire:model="productPricesUpdate.alteration">
                    <x-slot:label>
                        <div class="flex gap-1.5">
                            <span>
                                {{ __('Alteration') }}
                            </span>
                            <template
                                x-if="$wire.productPricesUpdate.alteration !== null && $wire.productPricesUpdate.alteration != 0"
                            >
                                <x-label>
                                    <x-slot:word>
                                        <div
                                            x-text="
                                                '(' +
                                                    ($wire.productPricesUpdate.alteration < 0
                                                        ? '{{ __('Reduce') }}'
                                                        : '{{ __('Increase') }}') +
                                                    ' ' +
                                                    $wire.productPricesUpdate.alteration +
                                                    ($wire.productPricesUpdate.is_percent
                                                        ? '%)'
                                                        : '{{ resolve_static(\FluxErp\Models\Currency::class, 'default')?->symbol }})')
                                            "
                                        />
                                    </x-slot>
                                </x-label>
                            </template>
                        </div>
                    </x-slot>
                </x-number>
                <x-select.styled
                    wire:model="productPricesUpdate.rounding_method_enum"
                    :label="__('Rounding Method')"
                    :options="$roundingMethods"
                />
                <div
                    x-show="$wire.productPricesUpdate.rounding_method_enum !== 'none'"
                >
                    <x-number
                        wire:model.number="productPricesUpdate.rounding_precision"
                        :label="__('Rounding Precision')"
                    />
                </div>
                <div
                    x-show="['nearest', 'end'].includes($wire.productPricesUpdate.rounding_method_enum)"
                >
                    <x-number
                        wire:model.number="productPricesUpdate.rounding_number"
                        :label="__('Rounding Number')"
                        min="0"
                        step="1"
                    />
                </div>
                <div
                    x-show="['nearest', 'end'].includes($wire.productPricesUpdate.rounding_method_enum)"
                >
                    <x-select.styled
                        wire:model="productPricesUpdate.rounding_mode"
                        :label="__('Rounding Mode')"
                        :options="$roundingModes"
                    />
                </div>
            </div>
            <x-slot:footer>
                <x-button
                    color="secondary"
                    light
                    flat
                    :text="__('Cancel')"
                    x-on:click="$modalClose('update-prices-modal')"
                />
                <x-button
                    loading="updatePrices"
                    color="indigo"
                    :text="__('Save')"
                    wire:flux-confirm.type.warning="{{ __('wire:confirm.product-prices-update') }}"
                    wire:click="updatePrices().then((success) => {if(success) $modalClose('update-prices-modal');});"
                />
            </x-slot>
        </x-modal>
    @endcanAction
</div>
