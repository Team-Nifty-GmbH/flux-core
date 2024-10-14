<x-modal name="edit-media">
    <x-card>
        <div class="flex flex-col gap-1.5">
            @section('media-attributes')
                <x-input :label="__('Name')" wire:model="mediaForm.name" />
                <x-input :label="__('File Name')" wire:model="mediaForm.file_name" />
                <x-input :label="__('File type')" disabled x-bind:value="$wire.mediaForm.file_name?.split('.').pop()" />
                <x-input :label="__('Disk')" disabled x-bind:value="$wire.mediaForm.disk" />
                <template x-for="(value, property) in $wire.mediaForm.custom_properties">
                    <div>
                        <template x-if="typeof value === 'boolean'">
                            <div class="flex gap-1.5">
                                <x-checkbox
                                    x-bind:id="'custom-attribute' + property"
                                    x-model.boolean="$wire.mediaForm.custom_properties[property]"
                                />
                                <label
                                    x-text="property"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-50"
                                    x-bind:for="'custom-attribute' + property"
                                >
                                </label>
                            </div>
                        </template>
                        <template x-if="typeof value === 'string'">
                            <div class="flex flex-col gap-1">
                                <label
                                    x-text="property"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-50"
                                    x-bind:for="'custom-attribute' + property"
                                >
                                </label>
                                <x-input
                                    x-bind:id="'custom-attribute' + property"
                                    x-model="$wire.mediaForm.custom_properties[property]"
                                />
                            </div>
                        </template>
                    </div>
                </template>
            @show
        </div>
        <x-slot:footer>
            <div class="flex justify-end gap-1.5">
                <x-button
                    x-on:click="close()"
                    :label="__('Cancel')"
                />
                <x-button
                    wire:click="save().then((success) => {if(success) close()})"
                    primary
                    :label="__('Save')"
                />
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>
