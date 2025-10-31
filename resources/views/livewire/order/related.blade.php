<div class="grid grid-cols-1 gap-8">
    @if ($order->parent_id)
        <x-card>
            <livewire:widgets.order :model-id="$order->parent_id" />
        </x-card>
        <x-card :header="__('Descending from the original order')">
            <livewire:order.related.family-orders :order-id="$order->id" lazy />
        </x-card>
    @endif

    <x-card :header="__('Descending from this order')">
        <livewire:order.related.descendant-orders :order-id="$order->id" lazy />
    </x-card>
    <x-card :header="__('Projects')">
        <livewire:order.related.projects :order-id="$order->id" lazy />
    </x-card>
    <x-card :header="__('Tickets')">
        <livewire:order.related.tickets :model-id="$order->id" lazy />
    </x-card>
</div>
