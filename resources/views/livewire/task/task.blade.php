<div
    id="task-details"
    x-data="{
        task: $wire.entangle('task'),
        showModal(id) {
            $wire.showTask(id).then(() => {
                Alpine.$data(document.getElementById('task-modal').querySelector('[wireui-modal]')).open()
            })
        },
        save() {
            $wire.save().then((task) => {
                if (task) {
                    $wire.dispatchTo('data-tables.tasks-list', 'fetchRecord', {record: task, event: task.hasOwnProperty('id') ? 'updated' : 'created'});
                    close();
                }
            });
        },
        delete() {
            window.$wireui.confirmDialog(
                {
                    title: '{{ __('Delete task') }}',
                    description: '{{ __('Do you really want to delete this task?') }}',
                    icon: 'error',
                    accept: {
                        label: '{{ __('Delete') }}',
                        execute: () => {
                            $wire.delete().then((success) => {
                                if (success) {
                                    $wire.dispatchTo('data-tables.tasks-list', 'fetchRecord', {record: this.task, event: 'deleted'});
                                    close();
                                }
                            });
                        },
                    },
                    reject: {
                        label: '{{ __('Cancel') }}',
                    }
                },
                $wire.__instance.id
            );
        }
    }"
    x-on:data-table-row-clicked.window="showModal($event.detail.id);"
    x-on:new-project-task.window="showModal();"
>
    <x-tabs
        wire:model.live="taskTab"
        :$tabs
        x-bind:disabled="! task.id"
    />
    <x-slot:footer>
        <div class="flex justify-between gap-x-4"
             x-data="{task: {id: null}}"
             x-on:data-table-row-clicked.window="task.id = $event.detail.id"
        >
            <div x-show="projectTask.id">
                <x-button
                    flat
                    negative
                    :label="__('Delete')"
                    x-on:click="Alpine.$data(document.getElementById('task-details')).delete()"
                />
            </div>
            <div class="flex w-full justify-end">
                <x-button flat :label="__('Cancel')" x-on:click="close" />
                <x-button primary :label="__('Save')" x-on:click="Alpine.$data(document.getElementById('task-details')).save()" />
            </div>
        </div>
    </x-slot:footer>
</div>
