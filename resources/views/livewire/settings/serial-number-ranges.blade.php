<x-modal id="edit-serial-number-range-modal">
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-4" x-cloak x-show="! $wire.serialNumberRange.id">
            <x-select.styled
                wire:model="serialNumberRange.model_type"
                :label="__('Model')"
                :options="$models"
                select="label:value|value:label"
            />
            <x-select.styled
                wire:model="serialNumberRange.client_id"
                :label="__('Client')"
                :options="$clients"
                select="label:name|value:id"
            />
        </div>
        <x-input wire:model="serialNumberRange.type" :label="__('Type')" />
        <x-number x-cloak x-show="$wire.serialNumberRange.id" step="1" min="0" wire:model.number="serialNumberRange.current_number" :label="__('Current Number')" />
        <x-number step="1" min="1" wire:model.number="serialNumberRange.length" :label="__('Length')" />
        <x-input wire:model="serialNumberRange.prefix" :label="__('Prefix')" />
        <x-input wire:model="serialNumberRange.suffix" :label="__('Suffix')" />
        <x-textarea wire:model="serialNumberRange.description" :label="__('Description')" />
        <x-toggle wire:model="serialNumberRange.is_pre_filled" :label="__('Is Pre Filled')" />
        <x-toggle wire:model="serialNumberRange.stores_serial_numbers" :label="__('Stores Serial Numbers')" />
    </div>
    <x-slot:footer>
        <div class="flex justify-between gap-x-4">
            @if(resolve_static(\FluxErp\Actions\SerialNumberRange\DeleteSerialNumberRange::class, 'canPerformAction', [false]))
                <div x-bind:class="$wire.serialNumberRange.id > 0 || 'invisible'">
                    <x-button
                        flat
                        color="red"
                        :text="__('Delete')"
                        wire:click="delete().then((success) => { if(success) $modalClose('edit-serial-number-range-modal')})"
                        wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Serial Number Range')]) }}"
                    />
                </div>
            @endif
            <div class="flex">
                <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-serial-number-range-modal')"/>
                <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-serial-number-range-modal')})"/>
            </div>
        </div>
    </x-slot:footer>
</x-modal>
