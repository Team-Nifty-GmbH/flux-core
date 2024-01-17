<x-modal name="create-orders">
    <x-card>
        <div class="flex flex-col gap-4">
            <x-select
                :label="__('Product')"
                option-value="id"
                option-label="label"
                option-description="description"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Product::class),
                    'method' => 'POST',
                    'params' => [
                        'where' => [
                            [
                                'is_service',
                                '=',
                                true,
                            ],
                        ],
                    ],
                ]"
            ></x-select>
            <x-toggle :label="__('Add non billable times')" />
        </div>
    </x-card>
</x-modal>
