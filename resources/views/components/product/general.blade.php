<div class="space-y-5" x-data wire:key="products-general">
    <x-card class="space-y-2.5" :header="__('General')">
        @section('general')
        <x-input
            x-bind:readonly="!edit"
            label="{{ __('Product number') }}"
            wire:model="product.product_number"
        />
        <x-input
            x-bind:readonly="!edit"
            label="{{ __('Name') }}"
            wire:model="product.name"
        />
        <x-flux::editor
            x-model="edit"
            wire:model="product.description"
            :label="__('Description')"
        />
        @show
    </x-card>
    <x-card class="space-y-2.5" :header="__('Attributes')">
        @section('attributes')
        @section('bools')
        <x-checkbox
            x-bind:disabled="!edit"
            label="{{ __('Is active') }}"
            wire:model="product.is_active"
        />
        <x-checkbox
            x-bind:disabled="!edit"
            label="{{ __('Is highlight') }}"
            wire:model="product.is_highlight"
        />
        <x-checkbox
            x-bind:disabled="!edit"
            label="{{ __('Is NOS') }}"
            wire:model="product.is_nos"
        />
        <x-checkbox
            x-bind:disabled="!edit"
            label="{{ __('Export to Webshop') }}"
            wire:model="product.is_active_export_to_web_shop"
        />
        <x-checkbox
            x-bind:disabled="!edit"
            label="{{ __('Is service') }}"
            wire:model="product.is_service"
        />
        <div x-cloak x-show="$wire.product.is_service">
            <x-select.styled
                label="{{ __('Time unit') }}"
                wire:model="product.time_unit_enum"
                :options="\FluxErp\Enums\TimeUnitEnum::valuesLocalized()"
            />
        </div>
        @show
        <hr />
        <x-input
            x-bind:readonly="!edit"
            label="{{ __('EAN') }}"
            wire:model="product.ean"
        />
        <x-input
            x-bind:readonly="!edit"
            label="{{ __('Manufacturer product number') }}"
            wire:model="product.manufacturer_product_number"
        />
        <x-select.styled
            x-bind:readonly="!edit"
            label="{{ __('Unit') }}"
            wire:model.number="product.unit_id"
            select="label:name|value:id"
            :options="resolve_static(\FluxErp\Models\Unit::class, 'query')->get(['id', 'name'])->toArray()"
        />
        <div
            class="grid grid-cols-1 gap-4 sm:grid-cols-4"
            x-bind:class="!edit && 'pointer-events-none'"
        >
            <x-number
                x-bind:readonly="!edit"
                wire:model.number="product.dimension_length_mm"
            >
                <x-slot:label>
                    <div class="flex items-center justify-between">
                        <div>
                            {{ __('Length') }}
                        </div>
                        <div>
                            {{ __('mm') }}
                        </div>
                    </div>
                </x-slot>
            </x-number>
            <x-number
                x-bind:readonly="!edit"
                wire:model.number="product.dimension_width_mm"
            >
                <x-slot:label>
                    <div class="flex items-center justify-between">
                        <div>
                            {{ __('Width') }}
                        </div>
                        <div>
                            {{ __('mm') }}
                        </div>
                    </div>
                </x-slot>
            </x-number>
            <x-number
                x-bind:readonly="!edit"
                wire:model.number="product.dimension_height_mm"
            >
                <x-slot:label>
                    <div class="flex items-center justify-between">
                        <div>
                            {{ __('Height') }}
                        </div>
                        <div>
                            {{ __('mm') }}
                        </div>
                    </div>
                </x-slot>
            </x-number>
            <x-number
                x-bind:readonly="!edit"
                wire:model.number="product.weight_gram"
            >
                <x-slot:label>
                    <div class="flex items-center justify-between">
                        <div>
                            {{ __('Weight') }}
                        </div>
                        <div>
                            {{ __('Gram') }}
                        </div>
                    </div>
                </x-slot>
            </x-number>
            <x-number
                x-bind:readonly="!edit"
                wire:model.number="product.selling_unit"
            >
                <x-slot:label>
                    <div class="flex items-center justify-between">
                        <div>
                            {{ __('Selling Unit') }}
                        </div>
                        <div>
                            <x-tooltip
                                :text="__('Required to calculate the product\'s unit price. The value to be entered depends on the selected scale unit.<br><br>Unit price = (product price * basic unit) / selling unit.<br><br>Unit price not displayed, if selling unit and basic unit have the same value.')"
                            />
                        </div>
                    </div>
                </x-slot>
            </x-number>
            <x-number
                x-bind:readonly="!edit"
                wire:model.number="product.basic_unit"
            >
                <x-slot:label>
                    <div class="flex items-center justify-between">
                        <div>
                            {{ __('Basic Unit') }}
                        </div>
                        <div>
                            <x-tooltip
                                :text="__('Required to calculate the product\'s unit price. The value to be entered depends on the selected scale unit.<br><br>Unit price = (product price * basic unit) / selling unit.<br><br>Unit price not displayed, if selling unit and basic unit have the same value.')"
                            />
                        </div>
                    </div>
                </x-slot>
            </x-number>
        </div>
        @show
    </x-card>
    <x-card class="flex flex-col gap-4" :header="__('Assignment')">
        <x-select.styled
            multiple
            x-bind:disabled="!edit"
            wire:model.number="product.categories"
            :label="__('Categories')"
            select="label:label|value:id"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\Category::class),
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
        <x-select.styled
            multiple
            x-bind:disabled="!edit"
            wire:model.number="product.clients"
            :label="__('Clients')"
            select="label:name|value:id"
            :src="'logo_small_url'"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\Client::class),
                'method' => 'POST',
            ]"
        />
        <x-select.styled
            multiple
            x-bind:disabled="!edit"
            wire:model.number="product.tags"
            select="label:label|value:id"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\Tag::class),
                'method' => 'POST',
                'params' => [
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
            <x-slot:label>
                <div class="flex items-center gap-2">
                    <x-label :label="__('Tags')" />
                    @canAction(\FluxErp\Actions\Tag\CreateTag::class)
                        <x-button.circle
                            sm
                            icon="plus"
                            color="emerald"
                            wire:click="addTag($promptValue())"
                            wire:flux-confirm.prompt="{{ __('New Tag') }}||{{ __('Cancel') }}|{{ __('Save') }}"
                        />
                    @endcanAction
                </div>
            </x-slot>
        </x-select.styled>
        <x-tag
            :label="__('Search Aliases')"
            wire:model="product.search_aliases"
        />
    </x-card>
    <x-card
        class="dark:bg-secondary-700 space-y-2.5 bg-gray-50"
        :header="__('Product Properties')"
        x-data="{productPropertyGroup: null}"
    >
        @section('product-properties')
        <x-modal
            id="edit-product-properties-modal"
            size="6xl"
            :title="__('Edit Product Properties')"
        >
            <div
                class="flex gap-4"
                x-on:data-table-row-clicked="
                    $wire.loadProductProperties($event.detail.id ?? $event.detail.record.id)
                    productPropertyGroup = $event.detail.record ?? $event.detail
                "
            >
                <div class="flex-grow">
                    <livewire:product.product-property-group-list />
                </div>
                <div
                    x-collapse
                    x-show="Object.values($wire.productProperties).length > 0"
                    x-cloak
                    class="w-1/2"
                >
                    <x-card>
                        <x-slot:header>
                            <span x-text="productPropertyGroup?.name"></span>
                        </x-slot>
                        <template
                            x-for="productProperty in $wire.productProperties"
                            :key="productProperty.id"
                        >
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
                                ></label>
                            </div>
                        </template>
                    </x-card>
                </div>
            </div>
            <x-slot:footer>
                <x-button
                    color="secondary"
                    light
                    flat
                    :text="__('Cancel')"
                    x-on:click="$modalClose('edit-product-properties-modal')"
                />
                <x-button
                    color="indigo"
                    :text="__('Save')"
                    wire:click="addProductProperties().then(() => { $modalClose('edit-product-properties-modal'); })"
                />
            </x-slot>
        </x-modal>
        <x-button
            color="indigo"
            x-show="edit"
            x-cloak
            :text="__('Edit')"
            wire:click="showProductPropertiesModal"
        />
        <div class="grid grid-cols-3 gap-x-4">
            <template
                x-for="(propertyTypes, group) in $wire.displayedProductProperties"
                :key="group"
            >
                <div class="col-span-1 space-y-2">
                    <x-card>
                        <x-slot:title>
                            <span x-text="group"></span>
                        </x-slot>
                        <template
                            x-for="(displayedProperties, propertyType) in propertyTypes"
                        >
                            <div>
                                <div
                                    class="flex flex-wrap gap-1.5"
                                    x-cloak
                                    x-show="propertyType === 'option'"
                                >
                                    <template
                                        x-for="displayedProperty in displayedProperties"
                                    >
                                        <x-badge
                                            x-text="displayedProperty.name"
                                        />
                                    </template>
                                </div>
                                <div
                                    class="space-y-2.5"
                                    x-cloak
                                    x-show="propertyType !== 'option'"
                                >
                                    <template
                                        x-for="displayedProperty in displayedProperties"
                                    >
                                        <div>
                                            <div class="mb-1">
                                                <x-label
                                                    x-bind:for="'displayed-property-' + displayedProperty.id"
                                                    x-text="displayedProperty.name"
                                                />
                                            </div>
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
    @if ($this->additionalColumns)
        <x-card :header="__('Additional columns')">
            @section('additional-columns')
            <div class="flex flex-col gap-4">
                <x-flux::additional-columns
                    :table="false"
                    wire="product"
                    :model="\FluxErp\Models\Product::class"
                    :id="$this->product->id"
                />
            </div>
            @show
        </x-card>
    @endif

    <x-card class="flex flex-col gap-4" :header="__('Suppliers')">
        @section('suppliers')
        <template x-for="(supplier, index) in $wire.product.suppliers">
            <x-flux::list-item :item="[]">
                <x-slot:value>
                    <span x-text="supplier.main_address.name"></span>
                </x-slot>
                <x-slot:sub-value>
                    <div class="flex gap-2">
                        <span>{{ __('Customer Number') . ':' }}</span>
                        <span x-text="supplier.customer_number"></span>
                    </div>
                </x-slot>
                <x-slot:actions>
                    <x-input
                        x-bind:disabled="! edit"
                        x-model="supplier.manufacturer_product_number"
                        :label="__('Manufacturer product number')"
                    />
                    <x-number
                        x-bind:disabled="! edit"
                        x-model="supplier.purchase_price"
                        :label="__('Purchase Price')"
                        step="0.01"
                    />
                    <div class="mt-6">
                        <x-button
                            color="red"
                            icon="trash"
                            x-bind:disabled="!edit"
                            x-on:click="$wire.product.suppliers.splice(index, 1);"
                        />
                    </div>
                </x-slot>
            </x-flux::list-item>
        </template>
        <div x-show="edit" x-cloak x-transition>
            <x-select.styled
                :label="__('Contact')"
                select="label:label|value:contact_id"
                x-on:select="$wire.addSupplier($event.detail.select.value); clear();"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Address::class),
                    'method' => 'POST',
                    'params' => [
                        'where' => [
                            [
                                'is_main_address',
                                '=',
                                true,
                            ],
                        ],
                        'option-value' => 'contact_id',
                        'fields' => [
                            'contact_id',
                            'name',
                        ],
                        'with' => 'contact.media',
                    ],
                ]"
            />
        </div>
        @show
    </x-card>
</div>
