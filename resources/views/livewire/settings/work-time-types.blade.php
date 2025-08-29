<div>
    <x-modal id="edit-work-time-type-modal" size="xl">
        <x-slot:title>
            {{ $workTimeType->id ?? false ? __('Edit Work Time Type') : __('Create Work Time Type') }}
        </x-slot:title>
        
        <div class="flex flex-col gap-4">
            <x-input wire:model="workTimeType.name" :label="__('Name')" required />
            
            <x-toggle
                wire:model="workTimeType.is_billable"
                :label="__('Is Billable')"
            />
        </div>
        
        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$modalClose('edit-work-time-type-modal')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                wire:click="save"
            />
        </x-slot:footer>
    </x-modal>
</div>