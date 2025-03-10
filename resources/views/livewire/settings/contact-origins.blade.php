<x-modal id="edit-contact-origin-modal">
    <div class="flex flex-col gap-1.5">
        <x-input wire:model="contactOriginForm.name" :label="__('Name')" />
        <x-toggle wire:model.boolean="contactOriginForm.is_active" :label="__('Is Active')" />
    </div>
    <x-slot:footer>
        <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-contact-origin-modal')"/>
        <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-contact-origin-modal')})"/>
    </x-slot:footer>
</x-modal>
