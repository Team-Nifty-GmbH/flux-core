<x-modal id="edit-contact-origin">
    <div class="flex flex-col gap-4">
        <x-input wire:model="contactOriginForm.name" :label="__('Name')" />
        <x-toggle wire:model.boolean="contactOriginForm.is_active" :label="__('Is Active')" />
    </div>
    <x-slot:footer>
        <div class="flex justify-end gap-1.5">
            <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-contact-origin')"/>
            <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-contact-origin')})"/>
        </div>
    </x-slot:footer>
</x-modal>
