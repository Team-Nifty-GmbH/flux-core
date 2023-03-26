<div class="grid grid-cols-1 gap-8">
    @if($parentId)
        <x-card>
            <livewire:widgets.order :model-id="$parentId" />
        </x-card>
        <x-card :title="__('Descending from the original order')">
            <livewire:data-tables.order-list :filters="[['parent_id', '=', $parentId], ['id', '!=', $orderId]]" />
        </x-card>
    @endif
    <x-card :title="__('Descending from this order')">
        <livewire:data-tables.order-list :filters="[['parent_id', '=', $orderId]]" />
    </x-card>
    <x-card :title="__('Tickets')">
        <livewire:data-tables.ticket-list :filters="[['model_id', '=', $orderId], ['model_type', '=', \FluxErp\Models\Order::class]]" />
    </x-card>
</div>
