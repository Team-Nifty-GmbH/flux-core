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
            @if (resolve_static(\FluxErp\Actions\Task\DeleteTask::class, "canPerformAction", [false]))
                <x-button
                    light
                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Task')]) }}"
                    color="red"
                    :text="__('Delete')"
                    wire:click="delete()"
                />
            @endif

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
        </div>
    </div>
    <x-flux::tabs
        wire:model.live="taskTab"
        :$tabs
        wire:loading="taskTab"
        card
    />
</div>
