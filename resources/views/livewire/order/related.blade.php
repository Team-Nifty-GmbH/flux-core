<div class="grid grid-cols-1 gap-8">
    @if ($order->parent_id ||resolve_static(\FluxErp\Models\Order::class, 'query')->where('parent_id', $order->id)->exists())
        <x-card :header="__('Family Tree')">
            <livewire:family-tree
                :model-type="\FluxErp\Models\Order::class"
                :model-id="$order->id"
                lazy
            />
        </x-card>
    @endif

    @if ($order->parent_id)
        <x-card>
            <livewire:widgets.order :model-id="$order->parent_id" />
        </x-card>
        <x-card :header="__('Descending from the original order')">
            <livewire:order.related.family-orders :order-id="$order->id" lazy />
        </x-card>
    @endif

    @if (resolve_static(\FluxErp\Models\Order::class, 'query')->where('parent_id', $order->id)->exists())
        <x-card :header="__('Descending from this order')">
            <livewire:order.related.descendant-orders
                :order-id="$order->id"
                lazy
            />
        </x-card>
    @endif

    @if (resolve_static(\FluxErp\Models\Order::class, 'query')->where('created_from_id', $order->id)->exists())
        <x-card :header="__('Created from this order')">
            <livewire:order.related.created-orders
                :order-id="$order->id"
                lazy
            />
        </x-card>
    @endif

    @if (resolve_static(\FluxErp\Models\Project::class, 'query')->where('order_id', $order->id)->exists())
        <x-card :header="__('Projects')">
            <livewire:order.related.projects :order-id="$order->id" lazy />
        </x-card>
    @endif

    @if (resolve_static(\FluxErp\Models\Ticket::class, 'query')->where('model_id', $order->id)->where('model_type', morph_alias(\FluxErp\Models\Order::class))->exists())
        <x-card :header="__('Tickets')">
            <livewire:order.related.tickets :model-id="$order->id" lazy />
        </x-card>
    @endif
</div>
