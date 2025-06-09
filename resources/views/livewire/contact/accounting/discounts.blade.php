<x-modal wire:close="resetDiscount()" id="edit-discount-modal">
    <div class="flex flex-col gap-1.5">
        <x-select.styled
            :label="__('Type')"
            x-on:select="$wire.discountForm.model_id = null"
            wire:model="discountForm.model_type"
            :options="[
                [
                    'label' => __('Product'),
                    'value' => morph_alias(\FluxErp\Models\Product::class),
                ],
                [
                    'label' => __('Category'),
                    'value' => morph_alias(\FluxErp\Models\Category::class),
                ],
            ]"
        />
        <div
            x-cloak
            x-show="
                $wire.discountForm.model_type ===
                    '{{ morph_alias(\FluxErp\Models\Category::class) }}'
            "
        >
            <x-select.styled
                :label="__('Category')"
                wire:model="discountForm.model_id"
                select="label:name|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Category::class),
                    'method' => 'POST',
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
        <div
            x-cloak
            x-show="
                $wire.discountForm.model_type ===
                    '{{ morph_alias(\FluxErp\Models\Product::class) }}'
            "
        >
            <x-select.styled
                :label="__('Product')"
                wire:model="discountForm.model_id"
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
                        'with' => 'media',
                    ],
                ]"
            />
        </div>
        <x-toggle
            :label="__('Is Percentage')"
            wire:model="discountForm.is_percentage"
        />
        <div x-cloak x-show="$wire.discountForm.is_percentage">
            <x-number
                :label="__('Discount Percent')"
                wire:model="discountForm.discount"
                step="0.01"
                min="-100"
                max="100"
            />
        </div>
        <div x-cloak x-show="! $wire.discountForm.is_percentage">
            <x-number
                :label="__('Discount Flat')"
                wire:model="discountForm.discount"
                step="0.01"
            />
        </div>
    </div>
    <x-slot:footer>
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => {if(success) $modalClose('edit-discount-modal');})"
        />
    </x-slot>
</x-modal>
