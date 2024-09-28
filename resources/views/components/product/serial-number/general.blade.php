<div class="grid grid-cols-1 gap-4">
    <x-card>
        <div class="space-y-2.5">
            <div class="grid grid-cols-3 gap-4">
                <x-input
                    :label="__('Serial number')"
                    x-bind:readonly="! edit"
                    wire:model.blur="serialNumber.serial_number"
                />
            </div>
            <div class="grid grid-cols-3 gap-4">
                <template x-for="address in $wire.serialNumber.addresses ?? []">
                    <div class="space-y-2.5">
                        <x-input
                            :label="__('Customer')"
                            x-model="address.name"
                            disabled
                        />
                        <x-inputs.number :label="__('Quantity')" x-model="address.quantity" disabled/>
                    </div>
                </template>
            </div>
        </div>
    </x-card>
    <x-card :title="__('Additional columns')">
        <x-flux::additional-columns :model="\FluxErp\Models\SerialNumber::class" :id="$this->serialNumber->id ?? null" wire="serialNumber"/>
    </x-card>
    <x-errors />
    @if($this->serialNumber->id ?? false)
        <x-card :title="__('Files')">
            <livewire:folder-tree wire:key="{{ uniqid() }}" :model-type="\FluxErp\Models\SerialNumber::class" :model-id="$this->serialNumber->id ?? null" />
        </x-card>
    @endif
</div>
