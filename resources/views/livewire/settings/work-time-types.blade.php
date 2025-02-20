<x-modal id="edit-work-time-type-modal">
    <div class="flex flex-col gap-4">
        <x-input wire:model="workTimeType.name" :label="__('Name')" />
        <x-toggle wire:model="workTimeType.is_billable" :label="__('Is Billable')" />
    </div>
    <x-slot:footer>
        <div class="flex justify-between gap-x-4">
            @if(resolve_static(\FluxErp\Actions\WorkTimeType\DeleteWorkTimeType::class, 'canPerformAction', [false]))
                <div x-bind:class="$wire.workTimeType.id > 0 || 'invisible'">
                    <x-button
                        flat
                        color="red"
                        :text="__('Delete')"
                        wire:click="delete().then((success) => { if(success) $modalClose('edit-work-time-type-modal')})"
                        wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Work Time Type')]) }}"
                    />
                </div>
            @endif
            <div class="flex">
                <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-work-time-type-modal')"/>
                <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-work-time-type-modal')})"/>
            </div>
        </div>
    </x-slot:footer>
</x-modal>
