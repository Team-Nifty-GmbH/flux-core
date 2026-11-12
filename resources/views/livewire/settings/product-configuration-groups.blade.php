<div>
    <x-modal
        id="edit-product-configuration-group-modal"
        :title="__('Product Configuration Group')"
        size="4xl"
    >
        <div class="flex flex-col gap-4">
            <x-input
                wire:model="productConfigurationGroupForm.name"
                :label="__('Name')"
            />
            <x-textarea
                wire:model="productConfigurationGroupForm.description"
                :label="__('Description')"
            />
            <div class="grid grid-cols-2 gap-4">
                <x-select.native
                    wire:model="productConfigurationGroupForm.selection_type_enum"
                    :label="__('Selection Type Enum')"
                    select="label:label|value:value"
                    :options="$selectionTypes"
                />
                <x-select.native
                    wire:model="productConfigurationGroupForm.price_mode_enum"
                    :label="__('Price Mode Enum')"
                    select="label:label|value:value"
                    :options="$priceModes"
                />
            </div>
            <div
                class="grid grid-cols-2 gap-4"
                x-cloak
                x-show="
                    $wire.productConfigurationGroupForm.selection_type_enum ===
                    'multiple'
                "
            >
                <x-number
                    wire:model="productConfigurationGroupForm.min_selections"
                    :label="__('Min selections')"
                    min="0"
                />
                <x-number
                    wire:model="productConfigurationGroupForm.max_selections"
                    :label="__('Max selections')"
                    min="1"
                />
            </div>
            <div
                class="flex flex-col gap-2"
                x-cloak
                x-show="$wire.productConfigurationGroupForm.id"
            >
                <div class="flex items-center justify-between">
                    <x-label :label="__('Options')" />
                    <x-button
                        color="indigo"
                        icon="plus"
                        :text="__('Add option')"
                        x-on:click="$wire.editItem()"
                    />
                </div>
                <template x-for="item in $wire.items" x-bind:key="item.id">
                    <div
                        class="flex items-center justify-between gap-4 border-b py-2"
                    >
                        <span
                            class="font-semibold"
                            x-text="item.product_name"
                        ></span>
                        <div class="flex items-center gap-2">
                            <span x-text="item.surcharge"></span>
                            <x-button
                                color="indigo"
                                icon="pencil"
                                x-on:click="$wire.editItem(item.id)"
                            />
                            <x-button
                                color="red"
                                icon="trash"
                                wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Option')]) }}"
                                wire:click="deleteItem(item.id)"
                            />
                        </div>
                    </div>
                </template>
            </div>
            <div
                class="text-sm text-gray-500"
                x-cloak
                x-show="!$wire.productConfigurationGroupForm.id"
            >
                {{ __('Options can be added once the group is saved.') }}
            </div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="
                    $tsui.close.modal('edit-product-configuration-group-modal')
                "
            />
            <x-button
                color="indigo"
                loading="save"
                :text="__('Save')"
                x-on:click="
                    $wire.save().then((success) => {
                        if (success)
                            $tsui.close.modal(
                                'edit-product-configuration-group-modal',
                            );
                    })
                "
            />
        </x-slot:footer>
    </x-modal>
    <x-modal
        id="edit-product-configuration-group-item-modal"
        :title="__('Option')"
    >
        <div class="flex flex-col gap-4">
            <x-select.styled
                :label="__('Product')"
                wire:model="productConfigurationGroupItemForm.product_id"
                required
                select="label:label|value:id|description:product_number"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Product::class),
                    'method' => 'POST',
                    'params' => [
                        'fields' => [
                            'id',
                            'name',
                            'product_number',
                        ],
                        'where' => [
                            ['is_configurable', '=', false],
                            ['is_bundle', '=', false],
                            ['is_variant_parent', '=', false],
                        ],
                        'with' => 'media',
                    ],
                ]"
            />
            <x-number
                wire:model="productConfigurationGroupItemForm.surcharge"
                :label="__('Surcharge')"
                step="0.01"
            />
            <x-number
                wire:model="productConfigurationGroupItemForm.max_amount"
                :label="__('Max quantity')"
                min="1"
            />
            <x-number
                wire:model="productConfigurationGroupItemForm.sort"
                :label="__('Sort')"
                min="0"
            />
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="
                    $tsui.close.modal(
                        'edit-product-configuration-group-item-modal',
                    )
                "
            />
            <x-button
                color="indigo"
                loading="saveItem"
                :text="__('Save')"
                x-on:click="
                    $wire.saveItem().then((success) => {
                        if (success)
                            $tsui.close.modal(
                                'edit-product-configuration-group-item-modal',
                            );
                    })
                "
            />
        </x-slot:footer>
    </x-modal>
</div>
