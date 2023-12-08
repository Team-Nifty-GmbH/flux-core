<div
    id="task-details"
    x-data="{
        task: $wire.entangle('task'),
        edit: false,
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
                                    window.location.href = '{{ route('tasks') }}';
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
>
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5">
        <div class="flex items-center space-x-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex">
                        <div class="pl-2">
                            <span x-text="task.name">
                            </span>
                        </div>
                    </div>
                </h1>
            </div>
        </div>
        <div class="justify-stretch mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
            @if(user_can('action.task.delete'))
                <x-button negative label="{{ __('Delete') }}" x-on:click="deleteTask()"/>
            @endif
            <x-button
                primary
                x-show="!edit"
                class="w-full"
                x-on:click="edit = true"
                :label="__('Edit')"
            />
            <x-button
                x-cloak
                primary
                spinner
                x-show="edit"
                class="w-full"
                x-on:click="$wire.save().then((success) => {
                    edit = false;
                });"
                :label="__('Save')"
            />
            <x-button
                x-cloak
                primary
                spinner
                x-show="edit"
                class="w-full"
                x-on:click="edit = false; $wire.resetForm();"
                :label="__('Cancel')"
            />
        </div>
    </div>
    <x-tabs
        wire:model.live="taskTab"
        :$tabs
    />
</div>
