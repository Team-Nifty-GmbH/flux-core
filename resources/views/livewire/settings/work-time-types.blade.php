<div>
    <x-modal
        :id="$workTimeTypeForm->modalName()"
        size="xl"
        :title="__('Edit Work Time Type')"
    >
        <div class="flex flex-col gap-4">
            <x-input
                wire:model="workTimeType.name"
                :label="__('Name')"
                required
            />

            <x-toggle
                wire:model="workTimeType.is_billable"
                :label="__('Is Billable')"
            />
        </div>

        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$modalClose('{{ $workTimeTypeForm->modalName() }}')"
            />
            <x-button :text="__('Save')" color="primary" wire:click="save" />
        </x-slot>
    </x-modal>
</div>
