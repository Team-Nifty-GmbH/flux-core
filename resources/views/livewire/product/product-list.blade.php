<div x-data="{edit: true}">
    @canAction(\FluxErp\Actions\Product\CreateProduct::class)
        <x-modal id="create-product" :title="__('New Product')">
            <section class="flex flex-col gap-4">
                <x-input wire:model="product.product_number" :label="__('Product Number')" :placeholder="__('Leave empty to generate a new :attribute.', ['attribute' => __('Product Number')])" />
                <div x-show="$wire.productTypes.length" x-cloak>
                    <x-select.styled
                        wire:model="product.product_type"
                        :label="__('Product Type')"
                        required
                        select="label:label|value:value"
                        :options="$productTypes"
                    />
                </div>
                <x-input wire:model="product.name" :label="__('Name')" />
                <x-select.styled
                    multiple
                    x-bind:disabled="!edit"
                    wire:model.number="product.clients"
                    :label="__('Clients')"
                    select="label:name|value:id"
                    :src="'logo_small_url'"
                    :request="[
                        'url' => route('search', \FluxErp\Models\Client::class),
                        'method' => 'POST',
                    ]"
                />
                <x-flux::editor wire:model="product.description" :label="__('Description')" />
            </section>
            <section class="flex flex-col gap-4">
                <x-flux::product.prices :product="$product" />
            </section>
            <x-slot name="footer">
                <div class="flex justify-end gap-x-4">
                    <div class="flex">
                        <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('create-product')" />
                        <x-button loading="save" color="indigo" :text="__('Save')" wire:click="save" />
                    </div>
                </div>
            </x-slot>
        </x-modal>
    @endCanAction
    @canAction(\FluxErp\Actions\Product\ProductPricesUpdate::class)
        <x-modal id="update-prices" :title="__('Update prices')" class="flex flex-col gap-4">
            <x-select.styled
                :options="$priceLists = resolve_static(\FluxErp\Models\PriceList::class, 'query')->pluck('name', 'id')"
                option-key-value
                :text="__('Price List')"
                wire:model="productPricesUpdate.price_list_id"
            />
            <x-select.styled
                :options="$priceLists"
                option-key-value
                :clearable="true"
                :label="__('Use price from')"
                wire:model="productPricesUpdate.base_price_list_id"
            />
            <x-toggle wire:model="productPricesUpdate.is_percent" :label="__('Is Percentage')" />
            <x-number wire:model="productPricesUpdate.alteration">
                <x-slot:label>
                    <div class="flex gap-1.5">
                        <span>
                            {{ __('Alteration') }}
                        </span>
                        <template x-if="$wire.productPricesUpdate.alteration !== null && $wire.productPricesUpdate.alteration != 0">
                            <x-label>
                                <x-slot:word>
                                    <div x-text="'(' +
                                        ($wire.productPricesUpdate.alteration < 0 ? '{{ __('Reduce') }}' : '{{ __('Increase') }}')
                                        + ' ' + $wire.productPricesUpdate.alteration
                                        + ($wire.productPricesUpdate.is_percent ? '%)' : '{{ \FluxErp\Models\Currency::default()?->symbol }})')"
                                    />
                                </x-slot:word>
                            </x-label>
                        </template>
                    </div>
                </x-slot:label>
            </x-number>
            <x-select.styled
                wire:model="productPricesUpdate.rounding_method_enum"
                :label="__('Rounding Method')"
                :options="$roundingMethods"
                option-key-value
            />
            <div x-show="$wire.productPricesUpdate.rounding_method_enum !== 'none'">
                <x-number
                    wire:model.number="productPricesUpdate.rounding_precision"
                    :label="__('Rounding Precision')"
                />
            </div>
            <div x-show="['nearest', 'end'].includes($wire.productPricesUpdate.rounding_method_enum)">
                <x-number
                    wire:model.number="productPricesUpdate.rounding_number"
                    :label="__('Rounding Number')"
                    min="0"
                    step="1"
                />
            </div>
            <div x-show="['nearest', 'end'].includes($wire.productPricesUpdate.rounding_method_enum)">
                <x-select.styled
                    wire:model="productPricesUpdate.rounding_mode"
                    :label="__('Rounding Mode')"
                    :options="$roundingModes"
                    option-key-value
                />
            </div>
            <x-slot:footer>
                <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('update-prices')" />
                <x-button
                    loading="updatePrices"
                    color="indigo"
                    :text="__('Save')"
                    wire:flux-confirm.icon.warning="{{ __('wire:confirm.product-prices-update') }}"
                    wire:click="updatePrices().then((success) => {if(success) $modalClose('update-prices');});"
                />
            </x-slot:footer>
        </x-modal>
    @endCanAction
</div>
