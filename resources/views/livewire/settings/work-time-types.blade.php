<x-modal id="edit-work-time-type-modal">
    <div class="flex flex-col gap-1.5">
        <x-input wire:model="workTimeType.name" :label="__('Name')" />
        <div class="mt-2">
            <x-toggle wire:model="workTimeType.is_billable" :label="__('Is Billable')" />
        </div>
    </div>
    <x-slot:footer>
        <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-work-time-type-modal')"/>
        <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-work-time-type-modal')})"/>
    </x-slot:footer>
</x-modal>
