<div class="py-6">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="mb-6 sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold dark:text-white">{{ __('Permissions') }}</h1>
                <p class="mt-2 text-sm text-gray-300">{{ __('Here you can add permissions to roles and add users to roles...') }}</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <x-button primary :label="__('Add Role')" wire:click="togglePermissions()"/>
            </div>
        </div>
        <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            @foreach($roles as $role)
                <li class="col-span-1 flex flex-col divide-y divide-gray-200 rounded-lg bg-white text-center shadow">
                    <div class="flex flex-1 flex-col p-8">
                        <h3 class="text-lg font-medium text-gray-900">{{ $role['name'] }}</h3>
                    </div>
                    <div>
                        <div class="-mt-px flex divide-x divide-gray-200">
                            <div class="flex w-0 flex-1">
                                <a wire:click="toggleUsers({{ $role['id'] }})"
                                   class="relative -mr-px inline-flex w-0 flex-1 cursor-pointer items-center justify-center rounded-bl-lg border border-transparent py-4 text-sm font-medium text-gray-700 hover:text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="ml-3">{{ __('Toggle Users') }}</span>
                                </a>
                            </div>
                            @if($role['name'] !== 'Super Admin')
                                <div class="flex w-0 flex-1">
                                    <a wire:click="togglePermissions({{ $role['id'] }})"
                                       class="relative -mr-px inline-flex w-0 flex-1 cursor-pointer items-center justify-center rounded-bl-lg border border-transparent py-4 text-sm font-medium text-gray-700 hover:text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span class="ml-3">{{ __('Toggle Permissions') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        <x-modal.card max-width="sm" blur :title="$selectedRole['name'] ?? ''" wire:model="showToggleUsers">
            <div class="space-y-6">
                @foreach($users as $user)
                    <div class="flex">
                        <div class="flex-1 font-medium">{{ $user['name'] }}</div>
                        <div class="">
                            <x-checkbox wire:model="selectedUsers" :value="$user['id']"/>
                        </div>
                    </div>
                @endforeach
            </div>
            <x-slot name="footer">
                <div class="flex justify-end gap-x-4">
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="saveToggleUsers"/>
                    </div>
                </div>
            </x-slot>
        </x-modal.card>

        <x-modal.card max-width="md" blur :title="$selectedRole['name'] ?? __('New Role')"
                      wire:model="showTogglePermissions">
            <div class="space-y-6">
                <x-input wire:model="selectedRole.name" :label="__('Name')"/>
                <x-native-select
                    :label="__('Guard')"
                    :disabled="$selectedRole['id'] ?? false"
                    :options="$guards"
                    wire:model="selectedRole.guard_name"
                />
                <div>
                    <x-label :label="__('Permissions')"/>
                    <x-input wire:model.live.debounce.500ms="searchPermission" icon="search"/>
                    <div class="max-h-96 space-y-6 overflow-y-auto">
                        @foreach($permissions as $permission)
                            <div class="flex">
                                <div class="flex-1 font-medium">{{ __($permission['name']) }}</div>
                                <div class="">
                                    <x-checkbox wire:model="selectedRole.permissions" :value="$permission['id']"/>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <x-slot name="footer">
                <div
                    class="flex @if(($selectedRole['id'] ?? false) && auth()->user()->can('api.roles.{id}.delete')) justify-between @else justify-end @endif gap-x-4">
                    @if(($selectedRole['id'] ?? false) && auth()->user()->can('api.roles.{id}.delete'))
                        <x-button flat negative label="{{ __('Delete') }}" @click="
                                                        window.$wireui.confirmDialog({
                                                            title: '{{ __('Delete role') }}',
                                                            description: '{{ __('Do you really want to delete this role?') }}',
                                                            icon: 'error',
                                                            accept: {
                                                                label: '{{ __('Delete') }}',
                                                                method: 'delete',
                                                            },
                                                            reject: {
                                                                label: '{{ __('Cancel') }}',
                                                            }
                                                        }, $wire.__instance.id)
                                                        " label="{{ __('Delete') }}"/>
                    @endif
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="saveTogglePermissions"/>
                    </div>
                </div>
            </x-slot>
        </x-modal.card>
    </div>
</div>
