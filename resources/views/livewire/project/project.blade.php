<div x-data="{
    edit: false,
}">
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5"
    >
        <div class="flex items-center space-x-5">
            @section('project.title')
            @section('project.title.avatar')
            <label for="avatar" class="cursor-pointer">
                <x-avatar xl :image="$avatar" />
            </label>
            <input
                type="file"
                accept="image/*"
                id="avatar"
                class="hidden"
                wire:model.live="avatar"
            />
            @show
            @section('project.title.name')
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex">
                        <div class="pl-2">
                            <span
                                x-text="$wire.project.project_number"
                            ></span>
                            <span
                                class="opacity-40 transition-opacity hover:opacity-100"
                                x-text="$wire.project.name"
                            ></span>
                        </div>
                    </div>
                </h1>
                <a
                    class="flex gap-1.5 font-semibold opacity-40"
                    x-bind:href="$wire.project.parent?.url"
                    x-cloak
                    x-show="$wire.project.parent?.url"
                >
                    <x-icon name="link" class="h-4 w-4" />
                    <span x-text="$wire.project.parent?.label"></span>
                </a>
            </div>
            @show
            @show
        </div>
        <div
            class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-x-3 sm:space-y-0 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3"
        >
            @canAction(\FluxErp\Actions\Project\DeleteProject::class)
                <x-button
                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Project')]) }}"
                    color="red"
                    :text="__('Delete')"
                    wire:click="delete()"
                />
            @endcanAction

            <x-button
                color="indigo"
                x-cloak
                x-show="!edit"
                class="w-full"
                x-on:click="edit = true"
                :text="__('Edit')"
            />
            <x-button
                x-cloak
                color="indigo"
                loading="save"
                x-cloak
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
                loading="save"
                x-cloak
                x-show="edit"
                class="w-full"
                x-on:click="edit = false; $wire.resetForm();"
                :text="__('Cancel')"
            />
        </div>
    </div>
    <x-flux::tabs wire:model.live="tab" :$tabs />
</div>
