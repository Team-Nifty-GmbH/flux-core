<div x-data="{
    task: $wire.$entangle('task', false),
    edit: true,
}">
    <div id="new-task-modal">
        <x-modal id="task-form-modal">
            <x-flux::tabs
                wire:model.live="taskTab"
                wire:loading="taskTab"
                :$tabs
            />
            <x-slot:footer>
                <div class="flex justify-between gap-x-4">
                    <div x-bind:class="$wire.task.id > 0 || 'invisible'">
                        <x-button
                            flat
                            color="red"
                            :text="__('Delete')"
                            wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Task')]) }}"
                            wire:click="delete().then((success) => {
                                if (success) close();
                            })"
                        />
                    </div>
                    <div class="flex">
                        <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('task-form-modal')"/>
                        <x-button
                            color="indigo"
                            :text="__('Save')"
                            wire:click="save().then((success) => {
                                if (success) $modalClose('task-form-modal');
                            })"
                        />
                    </div>
                </div>
            </x-slot:footer>
        </x-modal>
    </div>
    <div wire:ignore x-on:data-table-row-clicked="$wire.edit($event.detail.id)">
        @include('tall-datatables::livewire.data-table')
    </div>
</div>
