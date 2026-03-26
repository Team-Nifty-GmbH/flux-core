<x-modal id="edit-industry-modal" :title="__('Industry')">
    <div class="flex flex-col gap-1.5">
        <x-input wire:model="industryForm.name" :label="__('Name')" />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('edit-industry-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
<<<<<<< HEAD
            wire:click="save().then((success) => { if(success) $tsui.close.modal('edit-industry-modal')})"
=======
            x-on:click="$wire.save().then((success) => { if(success) $modalClose('edit-industry-modal')})"
>>>>>>> feature/auto-inject-frontend-assets
        />
    </x-slot>
</x-modal>
