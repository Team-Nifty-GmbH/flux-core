<div>
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
    <div wire:ignore>
        @include('tall-datatables::livewire.data-table')
    </div>
</div>
