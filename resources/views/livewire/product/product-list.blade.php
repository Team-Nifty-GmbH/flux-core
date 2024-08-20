<div x-data="{edit: true}">
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
                <x-editor wire:model="product.description" :label="__('Description')" />
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
    <div wire:ignore>
        @include('tall-datatables::livewire.data-table')
    </div>
</div>
