<div
    x-init="
        bundleType = $wire.product.bundle_type_enum
        let ignoreNextChange = false

        $nextTick(() =>
            $wire.$watch('product.bundle_type_enum', (value, oldValue) => {
                if (ignoreNextChange) {
                    ignoreNextChange = false

                    return
                }

                if (value !== oldValue) {
                    $interaction('dialog')
                        .wireable()
                        .error(
                            '{{ __('Warning') }}',
                            '{{ __('Changing the bundle type will affect stock and price') }}',
                        )
                        .confirm('{{ __('Confirm') }}')
                        .cancel('{{ __('Cancel') }}', () => {
                            ignoreNextChange = true
                            $wire.product.bundle_type_enum = oldValue
                        })
                        .send()
                }
            }),
        )
    "
>
    <x-modal id="edit-bundle-product-modal" :title="__('Edit Bundle Product')">
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                class="pb-4"
                :label="__('Product')"
                wire:model="productBundleProductForm.bundle_product_id"
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
                            [
                                'id',
                                '!=',
                                $product->id,
                            ],
                        ],
                        'with' => 'media',
                    ],
                ]"
            />
            <x-number
                wire:model="productBundleProductForm.count"
                :label="__('Count')"
                :min="0.01"
            />
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('edit-bundle-product-modal')"
            />
            <x-button
                color="indigo"
                loading="save"
                :text="__('Save')"
                wire:click="save().then((success) => { if(success) $modalClose('edit-bundle-product-modal'); })"
            />
        </x-slot>
    </x-modal>
    <div
        class="flex flex-col gap-2"
        x-cloak
        x-show="$wire.product.bundle_products?.length > 0"
    >
        @foreach (\FluxErp\Enums\BundleTypeEnum::valuesLocalized() as $bundleType)
            <x-radio
                :id="'bundle-type-enum-' . data_get($bundleType, 'value') . '-radio'"
                name="bundle-type-enum-radio"
                wire:model="product.bundle_type_enum"
                x-bind:disabled="!edit"
                :label="data_get($bundleType, 'label')"
                :value="data_get($bundleType, 'value')"
            />
        @endforeach
    </div>
</div>
