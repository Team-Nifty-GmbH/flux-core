<div class="space-y-5"
     x-data
     wire:key="products-general"
>
    <x-card class="space-y-2.5" :title="__('General')">
        @section('general')
        <x-input x-bind:readonly="!edit" label="{{ __('Product number') }}" wire:model="product.product_number" />
        <x-input x-bind:readonly="!edit" label="{{ __('Name') }}" wire:model="product.name" />
        <x-editor x-model="edit" wire:model="product.description" :label="__('Description')" />
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
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-input :suffix="__('mm')" x-bind:readonly="!edit" label="{{ __('Length') }}" wire:model.number="product.dimension_length_mm" />
                <x-input :suffix="__('mm')" x-bind:readonly="!edit" label="{{ __('Width') }}" wire:model.number="product.dimension_width_mm" />
                <x-input :suffix="__('mm')" x-bind:readonly="!edit" label="{{ __('Height') }}" wire:model.number="product.dimension_height_mm" />
                <x-input :suffix="__('Gram')" x-bind:readonly="!edit" label="{{ __('Weight') }}" wire:model.number="product.weight_gram" />
                <x-input x-bind:readonly="!edit" label="{{ __('Selling unit') }}" wire:model.number="product.selling_unit" />
                <x-input x-bind:readonly="!edit" label="{{ __('Basic unit') }}" wire:model.number="product.basic_unit" />
            </div>
        @show
    </x-card>
    <x-card class="space-y-2.5" :title="__('Product Properties')">
        @section('product-properties')
            <x-modal name="edit-product-properties-modal" max-width="6xl">
                <x-card :title="__('Edit Product Properties')" x-data="{productPropertyGroup: null}">
                    <div class="flex gap-4"
                         x-on:data-table-row-clicked="$wire.loadProductProperties($event.detail.id ?? $event.detail.record.id); productPropertyGroup = $event.detail.record ?? $event.detail;"
                    >
                        <div class="flex-grow">
                            <livewire:product.product-property-group-list />
                        </div>
                        <div x-collapse x-show="Object.values($wire.productProperties).length > 0" class="w-1/2">
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
                                :label="__('Add')"
                                wire:click="addProductProperties().then(() => { close(); })"
                            />
                        </div>
                    </x-slot:footer>
                </x-card>
            </x-modal>
            <x-button
                primary
                x-show="edit"
                :label="__('Add')"
                wire:click="showProductPropertiesModal"
            />
            <div class="grid grid-cols-3 gap-x-4">
                <template x-for="(propertyTypes, group) in $wire.displayedProductProperties" :key="group">
                    <div class="col-span-1 space-y-2">
                        <span x-text="group"></span>
                        <template x-for="(displayedProperties, propertyType) in propertyTypes">
                            <div>
                                <div class="flex space-x-1.5" x-cloak x-show="propertyType === 'option'">
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
                                            />
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
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
                            app(\FluxErp\Models\Product::class)->getMorphClass(),
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
                            app(\FluxErp\Models\Product::class)->getMorphClass(),
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
    @if($this->additionalColumns)
        <x-card :title="__('Additional columns')">
            @section('additional-columns')
                <div class="flex flex-col gap-4">
                    <x-additional-columns :table="false" wire="product" :model="\FluxErp\Models\Product::class" :id="$this->product->id" />
                </div>
            @show
        </x-card>
    @endif
    <x-card class="flex flex-col gap-4" :title="__('Suppliers')">
        @section('suppliers')
            <template x-for="(supplier, index) in $wire.product.suppliers">
                <x-list-item :item="[]">
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
                        <x-inputs.number x-bind:disabled="! edit" x-model="supplier.purchase_price" :label="__('Purchase Price')" step="0.01" />
                        <div class="mt-6">
                            <x-button
                                negative
                                icon="trash"
                                x-bind:disabled="!edit"
                                x-on:click="$wire.product.suppliers.splice(index, 1);"
                            />
                        </div>
                    </x-slot:actions>
                </x-list-item>
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
