<div>
    <x-modal id="edit-tag-modal">
        <div class="flex flex-col gap-4">
            <x-input wire:model="tagForm.name" :label="__('Name')" />
            <x-color-picker wire:model="tagForm.color" :label="__('Color')" />
            <div x-bind:class="$wire.tagForm.id && 'pointer-events-none'">
                <x-select.styled
                    x-bind:disabled="$wire.tagForm.id"
                    wire:model="tagForm.type"
                    select="label:value|value:label"
                    :label="__('Type')"
                    :options="$taggables"
                />
            </div>
        </div>
        <x-slot:footer>
            <div class="flex justify-end gap-4">
                <div class="flex">
                    <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-tag-modal')"/>
                    <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-tag-modal')})"/>
                </div>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
