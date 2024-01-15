<div>
    <x-modal name="edit-bundle-product-modal">
        <x-card :title="__('Edit Bundle Product')">
            <div class="flex flex-col gap-1.5">
                <x-select
                    class="pb-4"
                    :label="__('Product')"
                    wire:model="productBundleProductForm.bundle_product_id"
                    option-value="id"
                    option-label="label"
                    option-description="product_number"
                    :clearable="false"
                    :template="[
                        'name' => 'user-option',
                    ]"
                    :async-data="[
                        'api' => route('search', \FluxErp\Models\Product::class),
                        'params' => [
                            'fields' => ['id', 'name', 'product_number'],
                            'where' => [
                                [
                                    'id',
                                    '!=',
                                    $product->id,
                                ],
                            ],
                            'with' => 'media',
                        ]
                    ]"
                />
                <x-inputs.number
                    wire:model="productBundleProductForm.count"
                    :label="__('Count')"
                    :min="0.01"
                />
            </div>
            <x-slot:footer>
                <div class="flex justify-end gap-1.5">
                    <x-button
                        x-show="! Object.values($wire.variants).length > 0"
                        flat
                        :label="__('Cancel')"
                        x-on:click="close()"
                    />
                    <x-button
                        primary
                        spinner
                        :label="__('Save')"
                        wire:click="save().then((success) => { if(success) close(); })"
                    />
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <div wire:ignore>
        @include('tall-datatables::livewire.data-table')
    </div>
</div>
