<div class="gap-6 flex flex-col">
    <x-card>
        <x-slot:title>
            {{ __('Images') }}
        </x-slot:title>
        <livewire:data-tables.media-grid
            cache-key="product.media.media-grid"
            :is-searchable="false"
            :filters="[
                [
                    'model_id',
                    '=',
                    $this->product['id'],
                ],
                [
                    'model_type',
                    '=',
                    \FluxErp\Models\Product::class,
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
                $this->product['id'],
            ],
            [
                'model_type',
                '=',
                \FluxErp\Models\Product::class,
            ],
            [
                'collection_name',
                '!=',
                'images',
            ],
        ]"
    />
</div>
