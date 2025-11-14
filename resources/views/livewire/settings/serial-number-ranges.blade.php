<x-modal
    id="edit-serial-number-range-modal"
    :title="__('Serial Number Range')"
>
    <div class="flex flex-col gap-1.5">
        <div
            class="flex flex-col gap-1.5"
            x-cloak
            x-show="! $wire.serialNumberRange.id"
        >
            <x-select.styled
                wire:model="serialNumberRange.model_type"
                :label="__('Model')"
                :options="$models"
            />
            <x-select.styled
                wire:model="serialNumberRange.tenant_id"
                :label="__('Tenant')"
                select="label:name|value:id"
                :options="$tenants"
            />
        </div>
        <x-input wire:model="serialNumberRange.type" :label="__('Type')" />
        <div x-show="$wire.serialNumberRange.id" x-cloak>
            <x-number
                step="1"
                min="0"
                wire:model.number="serialNumberRange.current_number"
                :label="__('Current Number')"
            />
        </div>
        <x-number
            step="1"
            min="1"
            wire:model.number="serialNumberRange.length"
            :label="__('Length')"
        />
        <x-input wire:model="serialNumberRange.prefix" :label="__('Prefix')" />
        <x-input wire:model="serialNumberRange.suffix" :label="__('Suffix')" />
        <x-textarea
            wire:model="serialNumberRange.description"
            :label="__('Description')"
        />
        <div class="mt-2">
            <x-toggle
                wire:model="serialNumberRange.is_pre_filled"
                :label="__('Is Pre Filled')"
            />
        </div>
        <x-toggle
            wire:model="serialNumberRange.stores_serial_numbers"
            :label="__('Stores Serial Numbers')"
        />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-serial-number-range-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-serial-number-range-modal')})"
        />
    </x-slot>
</x-modal>
