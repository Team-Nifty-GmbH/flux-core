<div class="p-6">
    <div class="font-semibold text-2xl">
        <x-modal id="edit-unit-modal">
            <div class="flex flex-col gap-4">
                <x-input wire:model="unit.name" :label="__('Name')" />
                <x-input wire:model="unit.abbreviation" :label="__('Unit Abbreviation')" />
            </div>
            <x-slot:footer>
                <div class="flex justify-end gap-1.5">
                    <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-unit-modal')"/>
                    <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-unit-modal')})"/>
                </div>
            </x-slot:footer>
        </x-modal>
    </div>
</div>
