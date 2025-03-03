<x-modal id="edit-industry">
    <div class="flex flex-col gap-4">
        <x-input wire:model="industryForm.name" :label="__('Name')"/>
    </div>
    <x-slot:footer>
        <div class="flex justify-end gap-1.5">
            <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-industry')"/>
            <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-industry')})"/>
        </div>
    </x-slot:footer>
</x-modal>
