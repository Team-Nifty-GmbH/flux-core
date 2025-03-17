<x-modal
    id="edit-product-property-group-modal"
    :title="__('Edit Product Property Group')"
>
    <div class="flex flex-col gap-1.5">
        <x-input
            wire:model="productPropertyGroup.name"
            label="{{ __('Name') }}"
        />
        <div class="grid grid-cols-6 space-x-2">
            <div class="col-span-3">
                <x-label>{{ __('Product Properties') }}</x-label>
            </div>
            <div
                class="col-span-2"
                x-show="$wire.productPropertyGroup.product_properties.length > 0"
                x-cloak
            >
                <x-label :label="__('Property Type')" />
            </div>
        </div>
        <template
            x-for="(property, index) in $wire.productPropertyGroup.product_properties"
        >
            <div class="grid grid-cols-6 space-x-2">
                <div class="col-span-3">
                    <x-input
                        x-model="$wire.productPropertyGroup.product_properties[index].name"
                    />
                </div>
                <div class="col-span-2">
                    <x-select.native
                        x-model="$wire.productPropertyGroup.product_properties[index].property_type_enum"
                        required
                        select="label:label|value:name"
                        :options="$propertyTypes"
                    />
                </div>
                <div class="col-span-1 flex justify-end">
                    <x-button
                        color="red"
                        icon="trash"
                        x-on:click="$wire.productPropertyGroup.product_properties.splice(index, 1)"
                    />
                </div>
            </div>
        </template>
        <x-button
            color="indigo"
            x-on:click="$wire.productPropertyGroup.product_properties.push({name: '', property_type_enum: 'text'})"
        >
            {{ __('Add Product Property') }}
        </x-button>
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-product-property-group-modal')"
        />
        <x-button
            color="indigo"
            loading="save()"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-product-property-group-modal'); })"
        />
    </x-slot>
</x-modal>
