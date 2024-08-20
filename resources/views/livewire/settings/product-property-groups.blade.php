<x-modal name="edit-product-property-group" :title="__('Edit Product Property Group')">
    <x-card>
        <div class="flex flex-col gap-1.5">
            <x-input wire:model="productPropertyGroup.name" label="{{ __('Name') }}"/>
            <div class="grid grid-cols-6 space-x-2">
                <div class="col-span-3">
                    <x-label>{{ __('Product Properties') }}</x-label>
                </div>
                <div class="col-span-2" x-show="$wire.productPropertyGroup.product_properties.length > 0" x-cloak>
                    <x-label>{{ __('Property Type') }}</x-label>
                </div>
            </div>
            <template x-for="(property, index) in $wire.productPropertyGroup.product_properties">
                <div class="grid grid-cols-6 space-x-2">
                    <div class="col-span-3">
                        <x-input x-model="$wire.productPropertyGroup.product_properties[index].name"/>
                    </div>
                    <div class="col-span-2">
                        <x-native-select
                            x-model="$wire.productPropertyGroup.product_properties[index].property_type_enum"
                            :clearable="false"
                            option-value="name"
                            option-label="label"
                            :options="$propertyTypes"
                        />
                    </div>
                    <div class="col-span-1 flex justify-end">
                        <x-button negative icon="trash" x-on:click="$wire.productPropertyGroup.product_properties.splice(index, 1)" />
                    </div>
                </div>
            </template>
            <x-button primary x-on:click="$wire.productPropertyGroup.product_properties.push({name: '', property_type_enum: 'text'})">{{ __('Add Product Property') }}</x-button>
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
