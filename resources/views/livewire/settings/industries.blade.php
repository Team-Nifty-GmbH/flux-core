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
            x-on:click="$modalClose('edit-industry-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-industry-modal')})"
        />
    </x-slot>
</x-modal>
