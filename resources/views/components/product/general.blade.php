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
            @show
            <x-input x-bind:readonly="!edit" label="{{ __('EAN') }}" wire:model="product.ean" />
            <x-input x-bind:readonly="!edit" label="{{ __('Manufacturer product number') }}" wire:model="product.manufacturer_product_number" />
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-input suffix="mm" x-bind:readonly="!edit" label="{{ __('Length') }}" wire:model="product.dimension_length_mm" />
                <x-input suffix="mm" x-bind:readonly="!edit" label="{{ __('Width') }}" wire:model="product.dimension_width_mm" />
                <x-input suffix="mm" x-bind:readonly="!edit" label="{{ __('Height') }}" wire:model="product.dimension_height_mm" />
                <x-input :suffix="__('Gram')" x-bind:readonly="!edit" label="{{ __('Weight') }}" wire:model="product.weight_gram" />
            </div>
        @show
    </x-card>
    <x-card class="space-y-2.5" :title="__('Assignment')">
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
            ]"
        ></x-select>
        <x-select
            multiselect
            x-bind:disabled="!edit"
            wire:model.number="product.tags"
            :label="__('Tags')"
            option-value="description"
            option-label="label"
            :async-data="[
                'api' => route('search', \FluxErp\Models\Tag::class),
                'method' => 'POST',
                'params' => [
                    'where' => [
                        [
                            'type',
                            '=',
                            \FluxErp\Models\Product::class,
                        ],
                    ],
                ],
            ]"
        >
            <x-slot:beforeOptions>
                <div class="px-1">
                    <x-button positive full :label="__('Add')" wire:click="addTag($promptValue())" wire:confirm.prompt="{{  __('New Tag') }}||{{  __('Cancel') }}|{{  __('Save') }}" />
                </div>
            </x-slot:beforeOptions>
        </x-select>
    </x-card>
    @if($this->additionalColumns)
        <x-card :title="__('Additional columns')">
            <div class="flex flex-col gap-4">
                <x-additional-columns :table="false" wire="product" :model="\FluxErp\Models\Product::class" :id="$this->product->id" />
            </div>
        </x-card>
    @endif
</div>
