<div class="space-y-5"
     x-data
     wire:key="products-general"
>
    <x-card class="space-y-2.5" :title="__('General')">
        @section('general')
        <x-input x-bind:readonly="!edit" label="{{ __('Product number') }}" wire:model="product.product_number" />
        <x-input x-bind:readonly="!edit" label="{{ __('Name') }}" wire:model="product.name" />
        <x-flux::editor x-model="edit" wire:model="product.description" :label="__('Description')" />
        @show
    </x-card>
    <x-card class="space-y-2.5" :title="__('Attributes')">
        @section('attributes')
            @section('bools')
                <x-checkbox x-bind:disabled="!edit" label="{{ __('Is active') }}" wire:model="product.is_active" />
                <x-checkbox x-bind:disabled="!edit" label="{{ __('Is highlight') }}" wire:model="product.is_highlight" />
                <x-checkbox x-bind:disabled="!edit" label="{{ __('Is NOS') }}" wire:model="product.is_nos" />
                <x-checkbox x-bind:disabled="!edit" label="{{ __('Export to Webshop') }}" wire:model="product.is_active_export_to_web_shop" />
                <x-checkbox x-bind:disabled="!edit" label="{{ __('Is service') }}" wire:model="product.is_service" />
                <div x-cloak x-show="$wire.product.is_service">
                    <x-select label="{{ __('Time unit') }}"
                        option-key-value
                        wire:model="product.time_unit_enum"
                        :options="\FluxErp\Enums\TimeUnitEnum::valuesLocalized()"
                    />
                </div>
            @show
            <hr/>
            <x-input x-bind:readonly="!edit" label="{{ __('EAN') }}" wire:model="product.ean" />
            <x-input x-bind:readonly="!edit" label="{{ __('Manufacturer product number') }}" wire:model="product.manufacturer_product_number" />
            <x-select
                x-bind:readonly="!edit" label="{{ __('Unit') }}"
                option-key-value
                wire:model.number="product.unit_id"
                :options="resolve_static(\FluxErp\Models\Unit::class, 'query')->pluck('name', 'id')"
            />
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4" x-bind:class="!edit && 'pointer-events-none'">
                <template id="unit-price-tooltip">
                    <div class="p-1.5">
                        <div class="p-1.5">
                            {!! __('Required to calculate the product\'s unit price. The value to be entered depends on the selected scale unit.<br><br>Unit price = (product price * basic unit) / selling unit.<br><br>Unit price not displayed, if selling unit and basic unit have the same value.') !!}
                        </div>
                    </div>
                </template>
                <x-number x-bind:readonly="!edit" label="{{ __('Length') }}" wire:model.number="product.dimension_length_mm">
                    <x-slot:cornerHint>
                        <div class="flex gap-1.5 items-center">
                            <div class="text-secondary-400">
                                {{ __('mm') }}
                            </div>
                        </div>
                    </x-slot:cornerHint>
                </x-number>
                <x-number x-bind:readonly="!edit" label="{{ __('Width') }}" wire:model.number="product.dimension_width_mm">
                    <x-slot:cornerHint>
                        <div class="flex gap-1.5 items-center">
                            <div class="text-secondary-400">
                                {{ __('mm') }}
                            </div>
                        </div>
                    </x-slot:cornerHint>
                </x-number>
                <x-number x-bind:readonly="!edit" label="{{ __('Height') }}" wire:model.number="product.dimension_height_mm">
                    <x-slot:cornerHint>
                        <div class="flex gap-1.5 items-center">
                            <div class="text-secondary-400">
                                {{ __('mm') }}
                            </div>
                        </div>
                    </x-slot:cornerHint>
                </x-number>
                <x-number x-bind:readonly="!edit" label="{{ __('Weight') }}" wire:model.number="product.weight_gram">
                    <x-slot:cornerHint>
                        <div class="flex gap-1.5 items-center">
                            <div class="text-secondary-400">
                                {{ __('Gram') }}
                            </div>
                        </div>
                    </x-slot:cornerHint>
                </x-number>
                <x-number x-bind:readonly="!edit" label="{{ __('Selling unit') }}" wire:model.number="product.selling_unit">
                    <x-slot:cornerHint>
                        <x-mini-button rounded xs label="?" x-on:mouseover="$el._tippy ? $el._tippy.show() : tippy($el, {content: document.getElementById('unit-price-tooltip').content})" />
                    </x-slot:cornerHint>
                </x-number>
                <x-number x-bind:readonly="!edit" label="{{ __('Basic unit') }}" wire:model.number="product.basic_unit">
                    <x-slot:cornerHint>
                        <x-mini-button rounded xs label="?" x-on:mouseover="$el._tippy ? $el._tippy.show() : tippy($el, {content: document.getElementById('unit-price-tooltip').content})" />
                    </x-slot:cornerHint>
                </x-number>
            </div>
        @show
    </x-card>
    <x-card class="flex flex-col gap-1.5" :title="__('Assignment')">
        <x-select
            multiselect
            x-bind:disabled="!edit"
            wire:model.number="product.categories"
            :label="__('Categories')"
            option-value="id"
            option-label="label"
            option-description="description"
            :async-data="[
                'api' => route('search', \FluxErp\Models\Category::class),
                'method' => 'POST',
                'params' => [
                    'where' => [
                        [
                            'model_type',
                            '=',
                            morph_alias(\FluxErp\Models\Product::class),
                        ],
                    ],
                ],
            ]"
        />
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
        <x-select
            multiselect
            x-bind:disabled="!edit"
            wire:model.number="product.tags"
            :label="__('Tags')"
            option-value="id"
            option-label="label"
            :async-data="[
                'api' => route('search', \FluxErp\Models\Tag::class),
                'method' => 'POST',
                'params' => [
                    'option-value' => 'id',
                    'where' => [
                        [
                            'type',
                            '=',
                            morph_alias(\FluxErp\Models\Product::class),
                        ],
                    ],
                ],
            ]"
        >
            <x-slot:beforeOptions>
                <div class="px-1">
                    <x-button positive full :label="__('Add')" wire:click="addTag($promptValue())" wire:flux-confirm.prompt="{{ __('New Tag') }}||{{ __('Cancel') }}|{{ __('Save') }}" />
                </div>
            </x-slot:beforeOptions>
        </x-select>
    </x-card>
    <x-card class="space-y-2.5 bg-gray-50 dark:bg-secondary-700" :title="__('Product Properties')">
        @section('product-properties')
            <x-modal name="edit-product-properties-modal" max-width="6xl">
                <x-card :title="__('Edit Product Properties')" x-data="{productPropertyGroup: null}">
                    <div class="flex gap-4"
                         x-on:data-table-row-clicked="$wire.loadProductProperties($event.detail.id ?? $event.detail.record.id); productPropertyGroup = $event.detail.record ?? $event.detail;"
                    >
                        <div class="flex-grow">
                            <livewire:product.product-property-group-list />
                        </div>
                        <div x-collapse x-show="Object.values($wire.productProperties).length > 0" x-cloak class="w-1/2">
                            <x-card>
                                <x-slot:title>
                                    <span x-text="productPropertyGroup?.name"></span>
                                </x-slot:title>
                                <template x-for="productProperty in $wire.productProperties" :key="productProperty.id">
                                    <div class="flex gap-1.5">
                                        <x-checkbox
                                            x-bind:id="'product-property' + productProperty.id"
                                            x-bind:value="productProperty.id"
                                            x-model.number="$wire.selectedProductProperties[productProperty.id]"
                                        />
                                        <label
                                            x-text="productProperty.name"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-50"
                                            x-bind:for="'product-property' + productProperty.id"
                                        >
                                        </label>
                                    </div>
                                </template>
                            </x-card>
                        </div>
                    </div>
                    <x-slot:footer>
                        <div class="flex justify-end gap-1.5">
                            <x-button
                                flat
                                :label="__('Cancel')"
                                x-on:click="close()"
                            />
                            <x-button
                                primary
                                :label="__('Save')"
                                wire:click="addProductProperties().then(() => { close(); })"
                            />
                        </div>
                    </x-slot:footer>
                </x-card>
            </x-modal>
            <x-button
                primary
                x-show="edit"
                x-cloak
                :label="__('Edit')"
                wire:click="showProductPropertiesModal"
            />
            <div class="grid grid-cols-3 gap-x-4">
                <template x-for="(propertyTypes, group) in $wire.displayedProductProperties" :key="group">
                    <div class="col-span-1 space-y-2">
                        <x-card>
                            <x-slot:title>
                                <span x-text="group"></span>
                            </x-slot:title>
                            <template x-for="(displayedProperties, propertyType) in propertyTypes">
                                <div>
                                    <div class="flex flex-wrap gap-1.5" x-cloak x-show="propertyType === 'option'">
                                        <template x-for="displayedProperty in displayedProperties">
                                            <x-badge x-text="displayedProperty.name" />
                                        </template>
                                    </div>
                                    <div class="space-y-2.5" x-cloak x-show="propertyType !== 'option'">
                                        <template x-for="displayedProperty in displayedProperties">
                                            <div>
                                                <x-label
                                                    class="mb-1"
                                                    x-bind:for="'displayed-property-' + displayedProperty.id"
                                                    x-text="displayedProperty.name"
                                                ></x-label>
                                                <x-input
                                                    x-model="displayedProperty.value"
                                                    x-bind:id="'displayed-property-' + displayedProperty.id"
                                                    x-bind:disabled="!edit"
                                                />
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </x-card>
                    </div>
                </template>
            </div>
        @show
    </x-card>
    @if($this->additionalColumns)
        <x-card :title="__('Additional columns')">
            @section('additional-columns')
                <div class="flex flex-col gap-4">
                    <x-flux::additional-columns :table="false" wire="product" :model="\FluxErp\Models\Product::class" :id="$this->product->id" />
                </div>
            @show
        </x-card>
    @endif
    <x-card class="flex flex-col gap-4" :title="__('Suppliers')">
        @section('suppliers')
            <template x-for="(supplier, index) in $wire.product.suppliers">
                <x-flux::list-item :item="[]">
                    <x-slot:value>
                        <span x-text="supplier.main_address.name"></span>
                    </x-slot:value>
                    <x-slot:sub-value>
                        <div class="flex gap-2">
                            <span>{{ __('Customer Number') . ':' }}</span><span x-text="supplier.customer_number"></span>
                        </div>
                    </x-slot:sub-value>
                    <x-slot:actions>
                        <x-input x-bind:disabled="! edit" x-model="supplier.manufacturer_product_number" :label="__('Manufacturer product number')" />
                        <x-number x-bind:disabled="! edit" x-model="supplier.purchase_price" :label="__('Purchase Price')" step="0.01" />
                        <div class="mt-6">
                            <x-button
                                negative
                                icon="trash"
                                x-bind:disabled="!edit"
                                x-on:click="$wire.product.suppliers.splice(index, 1);"
                            />
                        </div>
                    </x-slot:actions>
                </x-flux::list-item>
            </template>
            <div x-show="edit" x-cloak x-transition>
                <x-select :label="__('Contact')"
                          option-value="contact_id"
                          option-label="label"
                          template="user-option"
                          x-on:selected="$wire.addSupplier($event.detail.value); clear();"
                          :async-data="[
                              'api' => route('search', \FluxErp\Models\Address::class),
                              'method' => 'POST',
                              'params' => [
                                  'where' => [
                                      [
                                          'is_main_address',
                                          '=',
                                          true,
                                      ]
                                  ],
                                  'option-value' => 'contact_id',
                                  'fields' => [
                                      'contact_id',
                                      'name',
                                  ],
                                  'with' => 'contact.media',
                              ]
                          ]"
                />
            </div>
        @show
    </x-card>
</div>
