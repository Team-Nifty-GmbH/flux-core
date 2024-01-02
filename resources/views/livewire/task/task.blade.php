<div
    id="task-details"
    x-data="{
        task: $wire.entangle('task'),
        edit: false
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
            @if(\FluxErp\Actions\Task\DeleteTask::canPerformAction(false))
                <x-button
                    wire:confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Task')]) }}"
                    negative
                    label="{{ __('Delete') }}"
                    wire:click="delete()"
                />
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
        wire:loading="taskTab"
        card
    />
</div>
