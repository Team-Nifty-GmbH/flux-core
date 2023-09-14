<div
    id="project-task-details"
    x-data="{
        projectTask: $wire.entangle('projectTask'),
        showModal(id) {
            $wire.showProjectTask(id).then(() => {
                Alpine.$data(document.getElementById('project-task-modal').querySelector('[wireui-modal]')).open()
            })
        },
        save() {
            $wire.save().then((task) => {
                if (task) {
                    $wire.dispatchTo('data-tables.project-tasks-list', 'refetchRecord', {record: task, event: task.hasOwnProperty('id') ? 'updated' : 'created'});
                    close();
                }
            });
        },
        delete() {
            window.$wireui.confirmDialog(
                {
                    title: '{{ __('Delete project task') }}',
                    description: '{{ __('Do you really want to delete this project task?') }}',
                    icon: 'error',
                    accept: {
                        label: '{{ __('Delete') }}',
                        execute: () => {
                            $wire.delete().then((success) => {
                                if (success) {
                                    $wire.dispatchTo('data-tables.project-tasks-list', 'refetchRecord', {record: this.projectTask, event: 'deleted'});
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
    x-on:data-table-row-clicked.window="showModal($event.detail.id)"
    x-on:new-project-task.window="showModal()"
>
    <x-tabs
        wire:model.live="tab"
        :tabs="$tabs"
        x-bind:disabled="! projectTask.id"
    >
        <x-errors />
        <x-spinner />
        <x-dynamic-component :component="'project-task.' . $tab" :project-task="$projectTask" />
    </x-tabs>
    <x-slot:footer>
        <div class="flex justify-between gap-x-4">
            <x-button
                flat
                negative
                :label="__('Delete')"
                x-on:click="Alpine.$data(document.getElementById('project-task-details')).delete()"
            />
            <div class="flex">
                <x-button flat :label="__('Cancel')" x-on:click="close" />
                <x-button primary :label="__('Save')" x-on:click="Alpine.$data(document.getElementById('project-task-details')).save()" />
            </div>
        </div>
    </x-slot:footer>
</div>
