<div class="space-y-5"
    x-data
    wire:key="{{ uniqid() }}"
>
    <x-card class="space-y-2.5" :title="__('General')">
        @section('general')
        <x-input x-bind:readonly="!edit" label="{{ __('Product number') }}" x-model="product.product_number" />
        <x-input x-bind:readonly="!edit" label="{{ __('Name') }}" x-model="product.name" />
        <x-textarea x-bind:readonly="!edit" label="{{ __('Description') }}" x-model="product.description" />
        @show
    </x-card>
    <x-card class="space-y-2.5" :title="__('Attributes')">
        @section('attributes')
        @section('bools')
        <x-checkbox x-bind:readonly="!edit" label="{{ __('Is active') }}" x-model="product.is_active" />
        <x-checkbox x-bind:readonly="!edit" label="{{ __('Is highlight') }}" x-model="product.is_highlight" />
        <x-checkbox x-bind:readonly="!edit" label="{{ __('Is NOS') }}" x-model="product.is_nos" />
        <x-checkbox x-bind:readonly="!edit" label="{{ __('Export to Webshop') }}" x-model="product.is_active_export_to_web_shop" />
        @show
        <x-input x-bind:readonly="!edit" label="{{ __('EAN') }}" x-model="product.ean" />
        <x-input x-bind:readonly="!edit" label="{{ __('Manufacturer product number') }}" x-model="product.manufacturer_product_number" />
        @show
    </x-card>
    <x-card class="space-y-2.5" :title="__('Assignment')">
        <x-model-select
            multiselect
            x-model="product.categories"
            :label="__('Categories')"
            option-value="id"
            option-label="label"
            option-description="description"
            :async-data="[
                'api' => route('search', \FluxErp\Models\Category::class),
                'method' => 'POST',
            ]"
        ></x-model-select>
        <x-model-select
            multiselect
            x-model="product.tags"
            :label="__('Tags')"
            option-value="description"
            option-label="label"
            :async-data="[
                'api' => route('search', \FluxErp\Models\Tag::class),
                'method' => 'POST',
            ]"
        ></x-model-select>
    </x-card>
    @if($this->additionalColumns)
        <x-card :title="__('Additional columns')">
            <div class="flex flex-col gap-4">
                <template x-for="additionalColumn in additionalColumns">
                    <div>
                        <x-label
                            x-html="additionalColumn.label ? additionalColumn.label : additionalColumn.name"
                            x-bind:for="additionalColumn.name"
                        />
                        <x-input x-bind:type="additionalColumn.field_type" x-model="product[additionalColumn.name]" :disabled="true"/>
                    </div>
                </template>
            </div>
        </x-card>
    @endif
</div>
