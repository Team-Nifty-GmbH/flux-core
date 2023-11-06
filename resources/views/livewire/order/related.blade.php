<div class="grid grid-cols-1 gap-8">
    @if($parentId)
        <x-card>
            <livewire:widgets.order :model-id="$parentId" />
        </x-card>
        <x-card :title="__('Descending from the original order')">
            <livewire:data-tables.order-list cache-key="order.related.order-list.siblings" :filters="[['parent_id', '=', $parentId], ['id', '!=', $orderId]]" />
        </x-card>
    @endif
    <x-card :title="__('Descending from this order')">
        <livewire:data-tables.order-list cache-key="order.related.order-list.children" :filters="[['parent_id', '=', $orderId]]" />
    </x-card>
    <x-card :title="__('Tickets')">
        <livewire:data-tables.ticket-list cache-key="order.related.ticket-list" :filters="[['model_id', '=', $orderId], ['model_type', '=', \FluxErp\Models\Order::class]]" :model-type="\FluxErp\Models\Order::class" :model-id="$orderId" />
    </x-card>
</div>
