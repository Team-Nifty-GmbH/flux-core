<div class="w-full">
    <x-card>
        <livewire:folder-tree
            :model-type="\FluxErp\Models\Order::class"
            :model-id="$this->order->id"
        />
    </x-card>
</div>
