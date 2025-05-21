<x-card>
    @section('user-edit')
    <form class="space-y-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @section('user-edit.personal-data')
            <x-input
                :label="__('Firstname')"
                wire:model="userForm.firstname"
            />
            <x-input :label="__('Lastname')" wire:model="userForm.lastname" />
            <x-input :label="__('Email')" wire:model="userForm.email" />
            <x-input :label="__('Phone')" wire:model="userForm.phone" />
            <x-input
                :label="__('User code')"
                wire:model="userForm.user_code"
            />
            <x-color :label="__('Color')" wire:model="userForm.color" />
            @show
        </div>
        <hr />
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @section('user-edit.employment')
            <x-date
                :without-time="true"
                :label="__('Date Of Birth')"
                wire:model="userForm.date_of_birth"
            />
            <x-input
                :label="__('Employee Number')"
                wire:model="userForm.employee_number"
            />
            <x-date
                :without-time="true"
                :label="__('Employment Date')"
                wire:model="userForm.employment_date"
            />
            <x-date
                :without-time="true"
                :label="__('Termination Date')"
                wire:model="userForm.termination_date"
            />
            <x-number
                :prefix="resolve_static(\FluxErp\Models\Currency::class, 'default')?->symbol"
                :label="__('Cost Per Hour')"
                wire:model="userForm.cost_per_hour"
            />
            @show
        </div>
        <hr />
        @section('user-edit.selects')
        <x-select.styled
            wire:model="userForm.language_id"
            :label="__('Language')"
            select="label:name|value:id"
            :options="$languages"
        />
        <x-select.styled
            wire:model="userForm.timezone"
            :label="__('Timezone')"
            :options="timezone_identifiers_list()"
        />
        <x-select.styled
            wire:model="userForm.parent_id"
            :label="__('Parent')"
            select="label:name|value:id|description:email"
            :options="$users"
        />
        @show
        @section('user-edit.attributes')
        <x-checkbox :label="__('Active')" wire:model="userForm.is_active" />
        <x-password
            :label="__('New password')"
            wire:model="userForm.password"
        />
        <x-password
            :label="__('Repeat password')"
            wire:model="userForm.password_confirmation"
        />
        @show
        <hr />
        @section('user-edit.bank-connection')
        <x-input
            wire:model="userForm.account_holder"
            :label="__('Account Holder')"
        />
        <x-input wire:model="userForm.iban" :label="__('IBAN')" />
        <x-input wire:model="userForm.bic" :label="__('BIC')" />
        <x-input wire:model="userForm.bank_name" :label="__('Bank Name')" />
        @show
        @section('user-edit.mail-accounts')
        <x-select.styled
            :label="__('Mail Accounts')"
            wire:model="userForm.mail_accounts"
            multiple
            select="label:email|value:id"
            :options="$mailAccounts"
        />
        @show
        @section('user-edit.printers')
        <x-select.styled
            :label="__('Printers')"
            wire:model="userForm.printers"
            multiple
            select="label:name|value:id|description:location"
            :options="$printers"
        />
        @show
        @section('user-edit.default-printer')
        @if ($userPrinters)
            <x-select.styled
                :label="__('Default Printer')"
                wire:model="printerUserForm.pivot_id"
                x-on:select="$tallstackuiSelect('default-printer-size').setOptions($event.detail.select.media_sizes)"
                select="label:name|value:id|description:location"
                :options="$userPrinters"
            />
            <div id="default-printer-size">
                <x-select.styled
                    :label="__('Default Size')"
                    wire:model="printerUserForm.default_size"
                    :options="data_get(collect($printers)->firstWhere('id', $printerUserForm->printer_id), 'media_sizes', [''])"
                />
            </div>
        @endif

        @show
    </form>
    @show
    <div
        class="border-b border-gray-200"
        x-data="{ active: 'roles', user: $wire.entangle('user') }"
    >
        <nav class="-mb-px mt-2 flex space-x-8 pb-5" aria-label="Tabs">
            <div
                x-on:click="active = 'roles'"
                x-bind:class="
                    active === 'roles'
                        ? 'border-purple-500 text-purple-600'
                        : 'border-transparent text-gray-500'
                "
                class="cursor-pointer whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium hover:border-gray-200 hover:text-gray-700"
            >
                {{ __('Roles') }}
            </div>
            <div
                x-on:click="active = 'permissions'"
                x-bind:class="
                    active === 'permissions'
                        ? 'border-purple-500 text-purple-600'
                        : 'border-transparent text-gray-500'
                "
                class="cursor-pointer whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium hover:border-gray-200 hover:text-gray-700"
            >
                {{ __('Permissions') }}
            </div>
            <div
                x-on:click="active = 'clients'"
                x-bind:class="
                    active === 'clients'
                        ? 'border-purple-500 text-purple-600'
                        : 'border-transparent text-gray-500'
                "
                class="cursor-pointer whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium hover:border-gray-200 hover:text-gray-700"
            >
                {{ __('Clients') }}
            </div>
            <div
                x-on:click="active = 'commission-rates'"
                x-bind:class="
                    active === 'commission-rates'
                        ? 'border-purple-500 text-purple-600'
                        : 'border-transparent text-gray-500'
                "
                class="cursor-pointer whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium hover:border-gray-200 hover:text-gray-700"
                x-show="$wire.userForm.id"
            >
                {{ __('Commission Rates') }}
            </div>
        </nav>
        @canAction(\FluxErp\Actions\Role\UpdateUserRoles::class)
            <div x-show="active === 'roles'" x-cloak>
                <div class="max-h-96 space-y-3 overflow-y-auto">
                    @php
                        $superAdmin = auth()
                            ->user()
                            ->hasRole('Super Admin');
                    @endphp

                    @foreach ($roles as $role)
                        @if ($role['name'] === 'Super Admin' && ! $superAdmin)
                            @continue
                        @endif

                        <div class="flex">
                            <div class="flex-1 text-sm">
                                {{ __($role['name']) }}
                            </div>
                            <div class="flex-1 text-sm">
                                {{ __($role['guard_name']) }}
                            </div>
                            <div class="pr-4">
                                <x-checkbox
                                    wire:model.number.live="userForm.roles"
                                    :value="$role['id']"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endcanAction

        @canAction(\FluxErp\Actions\Permission\UpdateUserPermissions::class)
            <div x-show="active === 'permissions'" x-cloak>
                <div class="pb-3">
                    <x-input
                        wire:model.live.debounce.500ms="searchPermission"
                        icon="magnifying-glass"
                    />
                </div>
                <div class="max-h-96 space-y-3 overflow-y-auto pt-3">
                    <div class="grid grid-cols-6 gap-3">
                        @foreach ($permissions as $permission)
                            <div class="col-span-3 font-medium">
                                {{ __($permission['name']) }}
                            </div>
                            <div class="font-medium">
                                {{ __($permission['guard_name']) }}
                            </div>
                            <x-checkbox
                                readonly
                                :label="__('Role')"
                                disabled
                                wire:model.number="lockedPermissions"
                                :value="$permission['id']"
                            />
                            <x-checkbox
                                :label="__('Direct')"
                                wire:model.number="userForm.permissions"
                                :value="$permission['id']"
                            />
                        @endforeach
                    </div>
                </div>
                <div class="pt-3">
                    {{ $permissions->links() }}
                </div>
            </div>
        @endcanAction

        @canAction(\FluxErp\Actions\User\UpdateUserClients::class)
            <div x-show="active === 'clients'" x-cloak>
                <div class="max-h-96 space-y-3 overflow-y-auto">
                    @foreach ($clients as $client)
                        <div class="flex">
                            <div class="flex-1 text-sm">
                                {{ $client['name'] }}
                            </div>
                            <div class="flex-1 text-sm">
                                {{ $client['client_code'] }}
                            </div>
                            <div class="pr-4">
                                <x-checkbox
                                    wire:model.number="userForm.clients"
                                    :value="$client['id']"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endcanAction

        <div x-show="active === 'commission-rates'" x-cloak>
            <x-select.styled
                :label="__('Commission credit contact')"
                class="pb-4"
                wire:model="userForm.contact_id"
                select="label:label|value:contact_id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Address::class),
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
                            ],
                        ],
                        'with' => [
                            'contact.media',
                            'country:id,name',
                        ],
                    ],
                ]"
            />
            <livewire:features.commission-rates
                lazy
                :user-id="$userForm->id"
                :contactId="null"
                cache-key="settings.users.commission-rates"
            />
        </div>
    </div>
    <x-slot:footer>
        <div class="w-full">
            <div class="flex justify-between gap-x-4">
                @canAction(\FluxErp\Actions\User\DeleteUser::class)
                    <x-button
                        color="red"
                        :text="__('Delete')"
                        wire:click="delete"
                        wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('User')]) }}"
                    />
                @endcanAction

                <div class="flex gap-x-2">
                    <x-button
                        color="secondary"
                        light
                        :text="__('Cancel')"
                        wire:click="cancel()"
                    />
                    <x-button
                        color="indigo"
                        :text="__('Save')"
                        wire:click="save()"
                    />
                </div>
            </div>
        </div>
    </x-slot>
</x-card>
