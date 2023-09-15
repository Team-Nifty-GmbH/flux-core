<div
    class="py-6"
    x-on:data-table-row-clicked="$wire.show($event.detail.id)"
>
    <x-modal.card x-on:close="$wire.closeModal()" z-index="z-30" wire:model="showUserModal" :title="__('Edit user')">
        <livewire:settings.user-edit/>
        <x-slot name="footer">
            <div x-data="{userId: $wire.entangle('userId')}" class="w-full">
                <div
                    class="flex justify-between gap-x-4">
                    @if(user_can('action.user.delete'))
                        <x-button
                            x-bind:class="userId > 0 || 'invisible'"
                            flat
                            negative
                            label="{{ __('Delete') }}"
                            x-on:click="
                                window.$wireui.confirmDialog({
                                    title: '{{ __('Delete user') }}',
                                    description: '{{ __('Do you really want to delete this user?') }}',
                                    icon: 'error',
                                    accept: {
                                        label: '{{ __('Delete') }}',
                                        method: 'delete',
                                    },
                                    reject: {
                                        label: '{{ __('Cancel') }}',
                                    }
                                }, $wire.__instance.id)
                                "
                            label="{{ __('Delete') }}"
                        />
                    @endif
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="$dispatchTo('settings.user-edit', 'save')"/>
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal.card>
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="mb-6 sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold dark:text-white">{{ __('Users') }}</h1>
                <p class="mt-2 text-sm text-gray-300">{{ __('Here you can manage the application users...') }}</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <x-button primary :label="__('New User')" wire:click="show()"/>
            </div>
        </div>
        <livewire:data-tables.user-list />
    </div>
</div>
