<x-modal
    id="edit-record-origin-modal"
    x-on:open="$focusOn('record-origin-name');"
>
    <div class="flex flex-col gap-1.5">
        <x-input
            id="record-origin-name"
            wire:model="recordOriginForm.name"
            :label="__('Name')"
        />
        <div x-cloak x-show="! $wire.recordOriginForm.id">
            <x-select.styled
                :label="__('Origin type')"
                wire:model="recordOriginForm.model_type"
                :options="$originTypeOptions"
                select="label:label|value:id"
                unfiltered
            />
        </div>
        <x-toggle
            wire:model.boolean="recordOriginForm.is_active"
            :label="__('Is Active')"
        />
    </div>

    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-record-origin-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-record-origin-modal') })"
        />
    </x-slot>
</x-modal>
