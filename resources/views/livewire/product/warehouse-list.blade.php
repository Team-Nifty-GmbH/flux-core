<div>
    <div class="lg:flex gap-6">
        <div class="flex-1" x-on:data-table-row-clicked="$wire.warehouseId = $event.detail.id;">
            @include('tall-datatables::livewire.data-table')
        </div>
        <div class="flex-grow">
            <livewire:product.stock-posting-list :product-id="$product->id" wire:model="warehouseId" />
        </div>
    </div>
</div>
