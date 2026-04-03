<x-modal id="edit-warehouse-modal" :title="__('Warehouse')">
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
            x-on:click="$tsui.close.modal('edit-warehouse-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="
                $wire.save().then((success) => {
                    if (success) $tsui.close.modal('edit-warehouse-modal');
                })
            "
        />
    </x-slot:footer>
</x-modal>
