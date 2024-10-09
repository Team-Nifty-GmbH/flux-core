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
                    morph_alias(\FluxErp\Models\Product::class),
                ],
                [
                    'collection_name',
                    '=',
                    'images',
                ],
            ]" />
    </x-card>
    <x-card :title="__('Other media')">
        <livewire:product.attachments :model-id="$this->product->id" />
    </x-card>
</div>
