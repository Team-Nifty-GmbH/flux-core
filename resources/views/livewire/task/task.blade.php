<div
    id="task-details"
    x-data="{
        task: $wire.entangle('task'),
        edit: false,
    }"
>
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5"
    >
        <div class="flex items-center space-x-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex">
                        <div class="pl-2">
                            <span x-text="task.name"></span>
                        </div>
                    </div>
                </h1>
            </div>
        </div>
        <div
            class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-x-3 sm:space-y-0 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3"
        >
            @canAction(\FluxErp\Actions\WorkTime\CreateWorkTime::class)
                <x-button
                    color="secondary"
                    light
                    icon="clock"
                    x-on:click="
                        $dispatch(
                            'start-time-tracking',
                            {
                                trackable_type: '{{ morph_alias(\FluxErp\Models\Task::class) }}',
                                trackable_id: {{ $task->id }},
                                name: {{ json_encode($task->name) }},
                                description: {{ json_encode(strip_tags($task->description ?? '')) }}
                            }
                        )"
                >
                    <div class="hidden sm:block">
                        {{ __('Track Time') }}
                    </div>
                </x-button>
            @endcanAction

            @canAction(\FluxErp\Actions\Task\DeleteTask::class)
                <x-button
                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Task')]) }}"
                    color="red"
                    :text="__('Delete')"
                    wire:click="delete()"
                />
            @endcanAction

            @canAction(\FluxErp\Actions\Task\UpdateTask::class)
                <x-button
                    color="indigo"
                    x-show="!edit"
                    class="w-full"
                    x-on:click="edit = true"
                    :text="__('Edit')"
                />
                <x-button
                    x-cloak
                    color="indigo"
                    loading
                    x-show="edit"
                    class="w-full"
                    x-on:click="$wire.save().then((success) => {
                        edit = false;
                    });"
                    :text="__('Save')"
                />
                <x-button
                    x-cloak
                    color="indigo"
                    loading
                    x-show="edit"
                    class="w-full"
                    x-on:click="edit = false; $wire.resetForm();"
                    :text="__('Cancel')"
                />
            @endcanAction
        </div>
    </div>
    <x-flux::tabs
        wire:model.live="taskTab"
        :$tabs
        wire:loading="taskTab"
        card
    />
</div>
