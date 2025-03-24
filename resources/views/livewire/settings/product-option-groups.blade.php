<x-modal
    id="edit-product-option-group-modal"
    :title="__('Edit Product Option Group')"
>
    <div class="flex flex-col gap-1.5">
        <x-input
            wire:model="productOptionGroupForm.name"
            label="{{ __('Name') }}"
        />
        <x-label>{{ __('Product Options') }}</x-label>
        <template
            x-for="(option, index) in $wire.productOptionGroupForm.product_options"
        >
            <div class="flex gap-1.5">
                <x-input
                    x-model="$wire.productOptionGroupForm.product_options[index].name"
                />
                <x-button
                    color="red"
                    icon="trash"
                    x-on:click="$wire.productOptionGroupForm.product_options.splice(index, 1)"
                />
            </div>
        </template>
        <x-button
            color="indigo"
            x-on:click="$wire.productOptionGroupForm.product_options.push({name: ''})"
        >
            {{ __('Add Product Option') }}
        </x-button>
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-product-option-group-modal')"
        />
        <x-button
            color="indigo"
            loading="save()"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-product-option-group-modal'); })"
        />
    </x-slot>
</x-modal>
