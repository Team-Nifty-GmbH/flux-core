<div x-data="{
        updateContactId(id) {
            Alpine.$data(
                document.getElementById('invoice-address-id').querySelector('[x-data]')
            ).asyncData.params.where[0][2] = id;
            Alpine.$data(
                document.getElementById('delivery-address-id').querySelector('[x-data]')
            ).asyncData.params.where[0][2] = id;
        }
    }">
    <x-modal name="create-product">
        <x-card :title="__('New Product')">
            <section class="flex flex-col gap-4">
                <x-input wire:model="product.product_number" :label="__('Product Number')" :placeholder="__('Leave empty to generate a new Product Number.')" />
                <x-input wire:model="product.name" :label="__('Name')" />
                <x-select wire:model="product.client_id" :label="__('Client')" :options="$clients" option-value="id" option-label="name"/>
                <x-editor wire:model="product.description" :label="__('Description')" />
                <x-select :options="$vatRates" label="{{ __('VAT rate') }}" wire:model="product.vat_rate_id" option-label="name" option-value="id"/>
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
