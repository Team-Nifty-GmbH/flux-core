<div class="p-6">
    <div class="font-semibold text-2xl">
        <x-modal name="edit-unit">
            <x-card>
                <div class="flex flex-col gap-4">
                    <x-input wire:model="unit.name" :label="__('Name')" />
                    <x-input wire:model="unit.abbreviation" :label="__('Unit Abbreviation')" />
                </div>
                <x-slot:footer>
                    <div class="flex justify-end gap-1.5">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close()})"/>
                    </div>
                </x-slot:footer>
            </x-card>
        </x-modal>
    </div>
</div>
