<x-card class="!px-0 !py-0">
    <livewire:features.comments.comments
        wire:key="{{ uniqid() }}"
        :model-type="\FluxErp\Models\SerialNumber::class"
        :model-id="$this->serialNumber->id"
    />
</x-card>
