<div>
    <x-modal name="edit-serial-number-range">
        <x-card>
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-4" x-cloak x-show="! $wire.serialNumberRange.id">
                    <x-select
                        wire:model="serialNumberRange.model_type"
                        :label="__('Model')"
                        :options="$models"
                    />
                    <x-select
                        wire:model="serialNumberRange.client_id"
                        :label="__('Client')"
                        :options="$clients"
                        option-label="name"
                        option-value="id"
                    />
                </div>
                <x-input wire:model="serialNumberRange.type" :label="__('Type')" />
                <x-inputs.number x-cloak x-show="$wire.serialNumberRange.id" step="1" min="0" wire:model.number="serialNumberRange.current_number" :label="__('Current Number')" />
                <x-inputs.number step="1" min="1" wire:model.number="serialNumberRange.length" :label="__('Length')" />
                <x-input wire:model="serialNumberRange.prefix" :label="__('Prefix')" />
                <x-input wire:model="serialNumberRange.suffix" :label="__('Suffix')" />
                <x-textarea wire:model="serialNumberRange.description" :label="__('Description')" />
                <x-toggle wire:model="serialNumberRange.is_pre_filled" :label="__('Is Pre Filled')" />
                <x-toggle wire:model="serialNumberRange.stores_serial_numbers" :label="__('Stores Serial Numbers')" />
            </div>
            <x-slot:footer>
                <div class="flex justify-between gap-x-4">
                    @if(\FluxErp\Actions\SerialNumberRange\DeleteSerialNumberRange::canPerformAction(false))
                        <div x-bind:class="$wire.serialNumberRange.id > 0 || 'invisible'">
                            <x-button
                                flat
                                negative
                                :label="__('Delete')"
                                x-on:click="close"
                                wire:click="delete().then((success) => { if(success) close()})"
                                wire:confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Serial Number Range')]) }}"
                            />
                        </div>
                    @endif
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close()})"/>
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <div wire:ignore>
        @include('tall-datatables::livewire.data-table')
    </div>
</div>
