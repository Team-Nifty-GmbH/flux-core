<div>
    @section('user-edit')
        <form class="space-y-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @section('user-edit.personal-data')
                    <x-input :label="__('Firstname')" wire:model="user.firstname"/>
                    <x-input :label="__('Lastname')" wire:model="user.lastname"/>
                    <x-input :label="__('Email')" wire:model="user.email"/>
                    <x-input :label="__('Phone')" wire:model="user.phone"/>
                    <x-input :label="__('User code')" wire:model="user.user_code"/>
                    <x-inputs.number
                        :prefix="\FluxErp\Models\Currency::default()?->symbol"
                        :label="__('Cost Per Hour')"
                        wire:model="user.cost_per_hour"
                    />
                @show
            </div>
            @section('user-edit.selects')
                <x-select
                    wire:model="user.language_id"
                    :label="__('Language')"
                    :options="$languages"
                    option-label="name"
                    option-value="id"
                />
                <x-select
                    wire:model="user.timezone"
                    :label="__('Timezone')"
                    :options="timezone_identifiers_list()"
                />
                <x-select
                    wire:model="user.parent_id"
                    :label="__('Parent')"
                    :options="$users"
                    option-label="name"
                    option-value="id"
                    option-description="email"
                />
            @show
            @section('user-edit.attributes')
                <x-checkbox :label="__('Active')" wire:model="user.is_active"/>
                <x-inputs.password :label="__('New password')" wire:model="user.password"/>
                <x-inputs.password :label="__('Repeat password')" wire:model="user.password_confirmation"/>
            @show
            @section('user-edit.bank-connection')
                <x-input wire:model="user.account_holder" :label="__('Account Holder')"/>
                <x-input wire:model="user.iban" :label="__('IBAN')"/>
                <x-input wire:model="user.bic" :label="__('BIC')"/>
                <x-input wire:model="user.bank_name" :label="__('Bank Name')"/>
            @show
            @section('user-edit.mail-accounts')
                <x-select :options="$mailAccounts" option-label="email" option-value="id" multiselect :label="__('Mail Accounts')" wire:model="user.mail_accounts" />
            @show
        </form>
    @show
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
            <div x-on:click="active = 'clients'"
                 x-bind:class="active === 'clients' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500'"
                 class="cursor-pointer whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium hover:border-gray-200 hover:text-gray-700">
                {{ __('Clients') }}
            </div>
            <div x-on:click="active = 'commission-rates'"
                 x-bind:class="active === 'commission-rates' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500'"
                 class="cursor-pointer whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium hover:border-gray-200 hover:text-gray-700"
                 x-show="user.id"
            >
                {{ __('Commission Rates') }}
            </div>
        </nav>
        @canAction(\FluxErp\Actions\Role\UpdateUserRoles::class)
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
                                <x-checkbox wire:model.number.live="user.roles" :value="$role['id']" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endCanAction
        @canAction(\FluxErp\Actions\Permission\UpdateUserPermissions::class)
            <div x-show="active === 'permissions'">
                <div class="pb-3">
                    <x-input wire:model.live.debounce.500ms="searchPermission" icon="search"/>
                </div>
                <div class="max-h-96 space-y-3 overflow-y-auto pt-3">
                    <div class="grid grid-cols-6 gap-3">
                        @foreach($permissions as $permission)
                            <div class="col-span-3 font-medium">{{ __($permission['name']) }}</div>
                            <div class="font-medium">{{ __($permission['guard_name']) }}</div>
                            <x-checkbox readonly :label="__('Role')" disabled wire:model.number="lockedPermissions"
                                        :value="$permission['id']" />
                            <x-checkbox :label="__('Direct')" wire:model.number="user.permissions"
                                        :value="$permission['id']" />
                        @endforeach
                    </div>
                </div>
                <div class="pt-3">
                    {{ $permissions->links() }}
                </div>
            </div>
        @endCanAction
        @canAction(\FluxErp\Actions\User\UpdateUserClients::class)
            <div x-cloak x-show="active === 'clients'">
                <div class="max-h-96 space-y-3 overflow-y-auto">
                    @foreach($clients as $client)
                        <div class="flex">
                            <div class="flex-1 text-sm">{{ $client['name'] }}</div>
                            <div class="flex-1 text-sm">{{ $client['client_code'] }}</div>
                            <div class="">
                                <x-checkbox wire:model.number="user.clients" :value="$client['id']" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endCanAction
        <div x-show="active === 'commission-rates'">
            <x-select
                :label="__('Commission credit contact')"
                class="pb-4"
                wire:model="user.contact_id"
                option-value="contact_id"
                option-label="label"
                option-description="description"
                template="user-option"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Address::class),
                    'method' => 'POST',
                    'params' => [
                        'option-value' => 'contact_id',
                        'fields' => [
                            'name',
                            'contact_id',
                            'firstname',
                            'lastname',
                            'company',
                        ],
                        'where' => [
                            [
                                'is_main_address',
                                '=',
                                true,
                            ]
                        ],
                        'with' => ['contact.media', 'country:id,name'],
                    ]
                ]"
            />
            <livewire:features.commission-rates :userId="$user['id'] ?? null" :contactId="null" cache-key="settings.users.commission-rates"/>
        </div>
    </div>
</div>
