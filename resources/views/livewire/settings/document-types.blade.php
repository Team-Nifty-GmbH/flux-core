<x-modal
    id="edit-document-type-modal"
    x-on:open="$tsui.focus('document-type-name')"
    :title="__('Document Type')"
>
    <div class="flex flex-col gap-1.5">
        <x-input
            id="document-type-name"
            wire:model="documentTypeForm.name"
            :label="__('Name')"
        />
        <x-textarea
            wire:model="documentTypeForm.description"
            :label="__('Description')"
        />
        <x-select.styled
            :label="__('Allowed for')"
            :hint="__('Leave empty to allow the document type everywhere.')"
            wire:model="documentTypeForm.model_types"
            multiple
            :options="$modelTypes"
        />
        <x-number
            wire:model="documentTypeForm.retention_years"
            :label="__('Retention in years')"
            :hint="__('Leave empty for documents without a retention lock.')"
            min="1"
            max="100"
        />
        <x-toggle
            wire:model.boolean="documentTypeForm.is_owner_uploadable"
            :label="__('Owner may upload')"
        />
        <x-toggle
            wire:model.boolean="documentTypeForm.is_active"
            :label="__('Is Active')"
        />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('edit-document-type-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="
                $wire.save().then((success) => {
                    if (success) $tsui.close.modal('edit-document-type-modal');
                })
            "
        />
    </x-slot:footer>
</x-modal>
