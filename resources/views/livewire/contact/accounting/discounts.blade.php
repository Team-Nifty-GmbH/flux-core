<x-modal wire:close="resetDiscount()" name="edit-discount">
    <x-card class="flex flex-col gap-4" footer-classes="flex justify-end gap-1.5">
        <x-select
            :options="[
                morph_alias(\FluxErp\Models\Product::class) => __('Product'),
                morph_alias(\FluxErp\Models\Category::class) => __('Category'),
            ]"
            option-key-value
            :label="__('Type')"
            x-on:selected="$wire.discountForm.model_id = null"
            wire:model="discountForm.model_type"
        />
        <div x-cloak x-show="$wire.discountForm.model_type === '{{ morph_alias(\FluxErp\Models\Category::class) }}'">
            <x-select
                :label="__('Category')"
                wire:model="discountForm.model_id"
                option-value="id"
                option-label="name"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Category::class),
                    'params' => [
                        'fields' => ['id', 'name'],
                        'where' => [
                            [
                                'model_type',
                                '=',
                                morph_alias(\FluxErp\Models\Product::class),
                            ],
                        ],
                    ]
                ]"
            />
        </div>
        <div x-cloak x-show="$wire.discountForm.model_type === '{{ morph_alias(\FluxErp\Models\Product::class) }}'">
            <x-select
                :label="__('Product')"
                wire:model="discountForm.model_id"
                option-value="id"
                option-label="label"
                option-description="product_number"
                :template="[
                    'name'   => 'user-option',
                ]"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Product::class),
                    'params' => [
                        'fields' => ['id', 'name', 'product_number'],
                        'with' => 'media',
                    ]
                ]"
            />
        </div>
        <x-toggle
            :label="__('Is Percentage')"
            wire:model="discountForm.is_percentage"
        />
        <x-inputs.number
             :label="__('Discount')"
             wire:model="discountForm.discount"
             step="0.01"
             min="0.01"
             max="99.99"
        />
        <x-slot:footer>
            <x-button
                primary
                :label="__('Save')"
                wire:click="save().then((success) => {if(success) close();})"
            />
        </x-slot:footer>
    </x-card>
</x-modal>
