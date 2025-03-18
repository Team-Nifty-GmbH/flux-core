<x-modal id="edit-warehouse-modal">
    <div class="flex flex-col gap-1.5">
        <x-input wire:model="warehouse.name" :label="__('Name')" />
        <div class="mt-2">
            <x-toggle
                wire:model.boolean="warehouse.is_default"
                :label="__('Is Default')"
            />
        </div>
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-warehouse-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-warehouse-modal')})"
        />
    </x-slot>
</x-modal>
