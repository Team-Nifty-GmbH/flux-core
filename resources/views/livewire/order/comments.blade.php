<x-card class="!px-0 !py-0">
    <livewire:features.comments.comments
        :model-type="\FluxErp\Models\Order::class"
        :model-id="$order->id"
    />
</x-card>
