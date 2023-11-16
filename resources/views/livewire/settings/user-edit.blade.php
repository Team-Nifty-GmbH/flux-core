<div>
    <form class="space-y-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-input :label="__('Firstname')" wire:model="user.firstname"/>
            <x-input :label="__('Lastname')" wire:model="user.lastname"/>
            <x-input :label="__('Email')" wire:model="user.email"/>
            <x-input :label="__('User code')" wire:model="user.user_code"/>
        </div>
        <x-select
            wire:model="user.language_id"
            :label="__('Language')"
            :options="$languages"
            option-label="name"
            option-value="id"
        />
        <x-select
            wire:model="user.parent_id"
            :label="__('Parent')"
            :options="$users"
            option-label="name"
            option-value="id"
            option-description="email"
        />
        <x-checkbox :label="__('Active')" wire:model="user.is_active"/>
        <x-inputs.password :label="__('New password')" wire:model="user.password"/>
        <x-inputs.password :label="__('Repeat password')" wire:model="user.password_confirmation"/>
        <x-select :options="$mailAccounts" option-label="email" option-value="id" multiselect :label="__('Mail Accounts')" wire:model="user.mail_accounts" />
    </form>
    <div class="border-b border-gray-200" x-data="{active: 'roles', user: $wire.entangle('user')}">
        <nav class="mt-2 -mb-px flex space-x-8 pb-5" aria-label="Tabs">
            <div x-on:click="active = 'roles'"
                 x-bind:class="active === 'roles' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500'"
                 class="cursor-pointer whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium hover:border-gray-200 hover:text-gray-700">
                {{ __('Roles') }}
            </div>
            <div x-on:click="active = 'permissions'"
                 x-bind:class="active === 'permissions' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500'"
                 class="cursor-pointer whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium hover:border-gray-200 hover:text-gray-700">
                {{ __('Permissions') }}
            </div>
            <div x-on:click="active = 'commission-rates'"
                 x-bind:class="active === 'commission-rates' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500'"
                 class="cursor-pointer whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium hover:border-gray-200 hover:text-gray-700"
                 x-show="user.id"
            >
                {{ __('Commission Rates') }}
            </div>
        </nav>
        @if(user_can('api.roles.{id}.get') && user_can('action.role.update-users'))
            <div x-show="active === 'roles'">
                <div class="max-h-96 space-y-3 overflow-y-auto">
                    @php
                        $superAdmin = auth()->user()->hasRole('Super Admin');
                    @endphp
                    @foreach($roles as $role)
                        @if($role['name'] === 'Super Admin' && !$superAdmin)
                            @continue
                        @endif
                        <div class="flex">
                            <div class="flex-1 text-sm">{{ __($role['name']) }}</div>
                            <div class="flex-1 text-sm">{{ __($role['guard_name']) }}</div>
                            <div class="">
                                <x-checkbox wire:model.live="user.roles" :value="$role['id']" :id="Str::uuid()->toString()"/>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @if(user_can('action.user.update-permissions'))
            <div x-show="active === 'permissions'">
                <div class="pb-3">
                    <x-input wire:model.live.debounce.500ms="searchPermission" icon="search"/>
                </div>
                <div class="max-h-96 space-y-3 overflow-y-auto pt-3">
                    <div class="grid grid-cols-6 gap-3">
                        @foreach($permissions as $permission)
                            <div class="col-span-3 font-medium">{{ __($permission['name']) }}</div>
                            <div class="font-medium">{{ __($permission['guard_name']) }}</div>
                            <x-checkbox readonly :label="__('Role')" disabled wire:model="lockedPermissions"
                                        :value="$permission['id']" :id="uniqid()"/>
                            <x-checkbox :label="__('Direct')" wire:model="user.permissions"
                                        :value="$permission['id']" :id="uniqid()"/>
                        @endforeach
                    </div>
                </div>
                <div class="pt-3">
                    {{ $permissions->links() }}
                </div>
            </div>
        @endif
        <div x-show="active === 'commission-rates'">
            <livewire:features.commission-rates :userId="$user['id'] ?? null" :contactId="null" cache-key="settings.users.commission-rates"/>
        </div>
    </div>
</div>
