<div class="grid grid-cols-1 gap-8">
    @if ($order->parent_id)
        <x-card>
            <livewire:widgets.order :model-id="$order->parent_id" />
        </x-card>
        <x-card :header="__('Descending from the original order')">
            <livewire:data-tables.order-list
                cache-key="order.related.order-list.siblings"
                :filters="[['parent_id', '=', $order->parent_id], ['id', '!=', $order->id]]"
            />
        </x-card>
    @endif

    <x-card :header="__('Descending from this order')">
        <livewire:data-tables.order-list
            cache-key="order.related.order-list.children"
            :filters="[['parent_id', '=', $order->id]]"
        />
    </x-card>
    <x-card :header="__('Projects')">
        <livewire:order.projects :order-id="$order->id" />
    </x-card>
    <x-card :header="__('Tickets')">
        <livewire:data-tables.ticket-list
            cache-key="order.related.ticket-list"
            :filters="[
                ['model_id', '=', $order->id],
                ['model_type', '=', morph_alias(\FluxErp\Models\Order::class)]
            ]"
            :model-type="\FluxErp\Models\Order::class"
            :model-id="$order->id"
        />
    </x-card>
</div>
