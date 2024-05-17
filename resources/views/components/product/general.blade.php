<div class="space-y-5"
    x-data
    wire:key="{{ uniqid() }}"
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
