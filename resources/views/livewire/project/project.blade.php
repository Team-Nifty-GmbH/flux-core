<div x-data="{
    project: $wire.entangle('project').defer,
    edit: false,
    deleteProject() {
        window.$wireui.confirmDialog(
            {
                title: '{{ __('Delete Project') }}',
                description: '{{ __('Do you really want to delete this Project?') }}',
                icon: 'error',
                accept: {
                    label: '{{ __('Delete') }}',
                    execute: () => {
                        $wire.delete().then((success) => {
                            if (success) {
                                window.location.href = '{{ route('projects') }}';
                                close();
                            }
                        });
                    },
                },
                reject: {
                    label: '{{ __('Cancel') }}',
                }
            },
            '{{ $this->id }}'
        );
    }
}"
>
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5">
        <div class="flex items-center space-x-5">
            <x-avatar xl :src="$project['avatar_url'] ?? ''"></x-avatar>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex">
                        <x-heroicons x-show="project.is_locked" variant="solid" name="lock-closed" />
                        <x-heroicons x-show="! project.is_locked" variant="solid" name="lock-open" />
                        <div class="pl-2">
                            <span x-text="project.project_name">
                            </span>
                            <span class="opacity-40 transition-opacity hover:opacity-100" x-text="project.display_name">
                            </span>
                        </div>
                    </div>
                </h1>
                <a class="flex gap-1.5 font-semibold opacity-40" x-bind:href="project.parent?.url" x-show="project.parent?.url">
                    <x-heroicons name="link" class="w-4 h-4" />
                    <span x-text="project.parent?.label">
                    </span>
                </a>
            </div>
        </div>
        <div class="justify-stretch mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
            @if(user_can('action.project.delete') && ($project['id'] ?? false))
                <x-button negative label="{{ __('Delete') }}" x-on:click="deleteProject()"/>
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
                x-on:click="edit = false"
                :label="__('Cancel')"
            />
        </div>
    </div>
    <x-tabs
        wire:model="tab"
        :tabs="$tabs"
        x-bind:disabled="! project.id"
        wire:ignore
    >
        <div class="w-full lg:col-start-1 xl:col-span-2 xl:flex xl:space-x-6">
            <section class="w-full lg:pt-0">
                <x-errors />
                <x-spinner />
                <x-dynamic-component :component="'project.' . $tab" :project="$project" :key="uniqid()" />
            </section>
        </div>
    </x-tabs>
</div>
