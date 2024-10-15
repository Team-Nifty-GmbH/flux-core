<x-card class="!py-0 !px-0">
    <livewire:features.comments.comments wire:key="{{ uniqid() }}" :model-type="\FluxErp\Models\SerialNumber::class" :model-id="$this->serialNumber->id" />
</x-card>
