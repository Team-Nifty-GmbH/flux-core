<div>
    <x-modal name="edit-product-option-group" :title="__('Edit Product Option Group')">
        <x-card>
            <div class="flex flex-col gap-1.5">
                <x-input wire:model="productOptionGroupForm.name" label="{{ __('Name') }}"/>
                <x-label>{{ __('Product Options') }}</x-label>
                <template x-for="(option, index) in $wire.productOptionGroupForm.product_options">
                    <div class="flex gap-1.5">
                        <x-input x-model="$wire.productOptionGroupForm.product_options[index].name"/>
                        <x-button negative icon="trash" x-on:click="$wire.productOptionGroupForm.product_options.splice(index, 1)" />
                    </div>
                </template>
                <x-button primary x-on:click="$wire.productOptionGroupForm.product_options.push({name: ''})">{{ __('Add Product Option') }}</x-button>
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
                        spinner="save()"
                        :label="__('Save')"
                        wire:click="save().then((success) => { if(success) close(); })"
                    />
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    @include('tall-datatables::livewire.data-table')
</div>
