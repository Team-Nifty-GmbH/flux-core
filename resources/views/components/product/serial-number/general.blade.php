<div class="grid grid-cols-1 gap-4">
    <x-card>
        <div class="grid grid-cols-1 gap-2">
            <x-input autofocus label="{{ __('Serial number') }}"
                     x-bind:readonly="! edit"
                     placeholder="{{ __('Enter new or chose an existing…') }}"
                     x-model="serialNumber.serial_number"/>

            <x-select
                class="pb-4"
                x-bind:disabled="! edit"
                :disabled="($this->serialNumber['product']['id'] ?? false) && ($this->serialNumber['id'] ?? false)"
                :label="__('Product')"
                wire:model.live="serialNumber.product_id"
                option-value="id"
                option-label="label"
                option-description="description"
                :clearable="false"
                :template="[
                    'name'   => 'user-option',
                ]"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Product::class),
                    'params' => [
                        'with' => 'media',
                    ]
                ]"
            />
            <x-select
                class="pb-4"
                x-bind:disabled="! edit"
                :label="__('Customer')"
                wire:model="serialNumber.address_id"
                option-value="id"
                option-label="label"
                option-description="description"
                :clearable="false"
                :template="[
                    'name'   => 'user-option',
                ]"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Address::class),
                    'params' => [
                        'with' => 'contact.media',
                    ]
                ]"
            >
            </x-select>
        </div>
    </x-card>
    <x-card :title="__('Additional columns')">
        <x-additional-columns :model="\FluxErp\Models\SerialNumber::class" :id="$this->serialNumber['id'] ?? null" wire="serialNumber"/>
    </x-card>
    <x-errors />
    @if($this->serialNumber['id'] ?? false)
        <x-card :title="__('Files')">
            <livewire:folder-tree wire:key="{{ uniqid() }}" :model-type="\FluxErp\Models\SerialNumber::class" :model-id="$this->serialNumber['id'] ?? null" />
        </x-card>
    @endif
</div>
