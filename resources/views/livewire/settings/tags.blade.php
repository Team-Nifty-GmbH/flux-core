<div>
    <x-modal name="edit-tag-modal">
        <x-card>
            <div class="flex flex-col gap-4">
                <x-input wire:model="tagForm.name" :label="__('Name')" />
                <x-color-picker wire:model="tagForm.color" :label="__('Color')" />
                <div x-bind:class="$wire.tagForm.id && 'pointer-events-none'">
                    <x-select
                        x-bind:disabled="$wire.tagForm.id"
                        wire:model="tagForm.type"
                        option-label="label"
                        option-value="value"
                        :label="__('Type')"
                        :options="$taggables"
                    />
                </div>
            </div>
            <x-slot:footer>
                <div class="flex justify-end gap-4">
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close()})"/>
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
</div>
