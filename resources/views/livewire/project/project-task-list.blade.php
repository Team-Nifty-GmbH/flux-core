<div x-data="{ isEditing: true }">
    <div id="new-task-modal">
        <x-modal
            size="5xl"
            id="task-form-modal"
            x-on:close="$wire.taskTab = 'task.general'"
        >
            <x-flux::tabs
                wire:model.live="taskTab"
                wire:loading="taskTab"
                :$tabs
            />
            <x-slot:footer>
                <div class="flex justify-between gap-x-4">
                    <div class="flex justify-end gap-x-2">
                        <x-button
                            color="secondary"
                            light
                            flat
                            :text="__('Cancel')"
                            x-on:click="$tsui.close.modal('task-form-modal')"
                        />
                        <x-button
                            color="indigo"
                            :text="__('Save')"
                            x-on:click="$wire.save().then((success) => {
                                if (success) $tsui.close.modal('task-form-modal');
                            })"
                        />
                    </div>
                </div>
            </x-slot>
        </x-modal>
    </div>
</div>
