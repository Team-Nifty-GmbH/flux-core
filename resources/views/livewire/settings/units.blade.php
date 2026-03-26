<div class="p-6">
    <div class="text-2xl font-semibold">
        <x-modal id="edit-unit-modal" :title="__('Unit')">
            <div class="flex flex-col gap-1.5">
                <x-input wire:model="unit.name" :label="__('Name')" />
                <x-input
                    wire:model="unit.abbreviation"
                    :label="__('Unit Abbreviation')"
                />
            </div>
            <x-slot:footer>
                <x-button
                    color="secondary"
                    light
                    flat
                    :text="__('Cancel')"
                    x-on:click="$tsui.close.modal('edit-unit-modal')"
                />
                <x-button
                    color="indigo"
                    :text="__('Save')"
<<<<<<< HEAD
                    wire:click="save().then((success) => { if(success) $tsui.close.modal('edit-unit-modal')})"
=======
                    x-on:click="$wire.save().then((success) => { if(success) $modalClose('edit-unit-modal')})"
>>>>>>> feature/auto-inject-frontend-assets
                />
            </x-slot>
        </x-modal>
    </div>
</div>
