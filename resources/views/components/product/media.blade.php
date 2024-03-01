<div class="gap-6 flex flex-col">
    <x-card>
        <x-slot:title>
            {{ __('Images') }}
        </x-slot:title>
        <livewire:data-tables.products.media-grid
            :is-searchable="false"
            wire:model="product"
            :filters="[
                [
                    'model_id',
                    '=',
                    $this->product->id,
                ],
                [
                    'model_type',
                    '=',
                    app(\FluxErp\Models\Product::class)->getMorphClass(),
                ],
                [
                    'collection_name',
                    '=',
                    'images',
                ],
            ]" />
    </x-card>
    <livewire:data-tables.media-list
        cache-key="product.media.media-list"
        :headline="__('Other media')"
        :filters="[
            [
                'model_id',
                '=',
                $this->product->id,
            ],
            [
                'model_type',
                '=',
                app(\FluxErp\Models\Product::class)->getMorphClass(),
            ],
            [
                'collection_name',
                '!=',
                'images',
            ],
        ]"
    />
</div>
