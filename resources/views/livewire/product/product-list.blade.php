<div x-data="{edit: true}">
    @canAction(\FluxErp\Actions\Product\CreateProduct::class)
        <x-modal name="create-product">
            <x-card :title="__('New Product')">
                <section class="flex flex-col gap-4">
                    <x-input wire:model="product.product_number" :label="__('Product Number')" :placeholder="__('Leave empty to generate a new :attribute.', ['attribute' => __('Product Number')])" />
                    <div x-show="$wire.productTypes.length" x-cloak>
                        <x-select
                            wire:model="product.product_type"
                            :label="__('Product Type')"
                            :clearable="false"
                            option-value="value"
                            option-label="label"
                            :options="$productTypes"
                        />
                    </div>
                    <x-input wire:model="product.name" :label="__('Name')" />
                    <x-select
                        multiselect
                        x-bind:disabled="!edit"
                        wire:model.number="product.clients"
                        :label="__('Clients')"
                        option-value="id"
                        option-label="name"
                        :src="'logo_small_url'"
                        template="user-option"
                        :async-data="[
                            'api' => route('search', \FluxErp\Models\Client::class),
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
                            <x-button flat :label="__('Cancel')" x-on:click="close" />
                            <x-button spinner primary :label="__('Save')" wire:click="save" />
                        </div>
                    </div>
                </x-slot>
            </x-card>
        </x-modal>
    @endCanAction
    @canAction(\FluxErp\Actions\Product\ProductPricesUpdate::class)
        <x-modal name="update-prices">
            <x-card :title="__('Update prices')" footer-classes="flex justify-end gap-1.5" class="flex flex-col gap-4">
                <x-select
                    :options="$priceLists = resolve_static(\FluxErp\Models\PriceList::class, 'query')->pluck('name', 'id')"
                    option-key-value
                    :label="__('Price List')"
                    wire:model="productPricesUpdate.price_list_id"
                />
                <x-select
                    :options="$priceLists"
                    option-key-value
                    :clearable="true"
                    :label="__('Use price from')"
                    wire:model="productPricesUpdate.base_price_list_id"
                />
                <x-toggle wire:model="productPricesUpdate.is_percent" :label="__('Is Percentage')" />
                <x-inputs.number wire:model="productPricesUpdate.alternation">
                    <x-slot:label>
                        <div class="flex gap-1.5">
                            <span>
                                {{ __('Alteration') }}
                            </span>
                            <template x-if="$wire.productPricesUpdate.alternation !== null && $wire.productPricesUpdate.alternation != 0">
                                <x-label>
                                    <div x-text="'(' +
                                        ($wire.productPricesUpdate.alternation < 0 ? '{{ __('Reduce') }}' : '{{ __('Increase') }}')
                                        + ' ' + $wire.productPricesUpdate.alternation
                                        + ($wire.productPricesUpdate.is_percent ? '%)' : '{{ \FluxErp\Models\Currency::default()?->symbol }})')"
                                    >
                                    </div>
                                </x-label>
                            </template>
                        </div>
                    </x-slot:label>
                </x-inputs.number>
                <x-select
                    wire:model="productPricesUpdate.rounding_method_enum"
                    :label="__('Rounding Method')"
                    :options="$roundingMethods"
                    option-key-value
                />
                <div x-show="$wire.productPricesUpdate.rounding_method_enum !== 'none'">
                    <x-inputs.number
                        wire:model.number="productPricesUpdate.rounding_precision"
                        :label="__('Rounding Precision')"
                    />
                </div>
                <div x-show="['nearest', 'end'].includes($wire.productPricesUpdate.rounding_method_enum)">
                    <x-inputs.number
                        wire:model.number="productPricesUpdate.rounding_number"
                        :label="__('Rounding Number')"
                        min="0"
                        step="1"
                    />
                </div>
                <div x-show="['nearest', 'end'].includes($wire.productPricesUpdate.rounding_method_enum)">
                    <x-select
                        wire:model="productPricesUpdate.rounding_mode"
                        :label="__('Rounding Mode')"
                        :options="$roundingModes"
                        option-key-value
                    />
                </div>
                <x-slot:footer>
                    <x-button flat :label="__('Cancel')" x-on:click="close" />
                    <x-button
                        spinner
                        primary
                        :label="__('Save')"
                        wire:flux-confirm.icon.warning="{{ __('wire:confirm.product-prices-update') }}"
                        wire:click="updatePrices().then((success) => {if(success) close();});"
                    />
                </x-slot:footer>
            </x-card>
        </x-modal>
    @endCanAction
</div>
