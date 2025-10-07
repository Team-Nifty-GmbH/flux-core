<div>
    <div class="gap-6 lg:flex">
        <div
            class="flex-1"
            x-on:data-table-row-clicked="$wire.warehouseId = $event.detail.record.id;"
        >
            @include('tall-datatables::livewire.data-table')
        </div>
        <div class="flex-grow">
            <livewire:product.stock-posting-list
                :product-id="$product->id"
                wire:model="warehouseId"
            />
        </div>
    </div>
</div>
