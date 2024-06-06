<div x-data="{
    address: $wire.entangle('address', true),
    showUserList: $wire.entangle('showUserList', true),
}">
    <div x-show="! showUserList">
        <div>
            <div class="flex w-full flex-col-reverse justify-between md:flex-row">
                <h2 class="pt-5 text-base font-bold uppercase dark:text-white md:pt-0">
                    {{ __('Edit profile') }}
                </h2>
            </div>
            <h1 class="pt-5 text-5xl font-bold dark:text-white">
                {{ ($address['id'] ?? false) ? trim($address['firstname'] . ' ' . $address['lastname']) : __('New address')}}
            </h1>
        </div>
        <form class="pt-12">
            @if(auth()->user()->can('profiles.{id?}.get'))
                <div class="flex w-full justify-end">
                    <x-button primary wire:click="showUsers" :label="__('Edit users')" />
                </div>
            @endif
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-gray-200 sm:pt-5">
                <label for="{{ md5('address.salutation') }}"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Salutation') }}
                </label>
                <div class="col-span-2">
                    <x-input wire:model="address.salutation"/>
                </div>
            </div>
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                <label for="{{ md5('address.title') }}"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Title') }}
                </label>
                <div class="col-span-2">
                    <x-input wire:model="address.title"/>
                </div>
            </div>
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                <label for="{{ md5('address.firstname') }}"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Firstname') }}
                </label>
                <div class="col-span-2">
                    <x-input wire:model="address.firstname"/>
                </div>
            </div>
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                <label for="{{ md5('address.lastname') }}"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Lastname') }}
                </label>
                <div class="col-span-2">
                    <x-input wire:model="address.lastname"/>
                </div>
            </div>
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                <label for="{{ md5('address.street') }}"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Street') }}
                </label>
                <div class="col-span-2">
                    <x-input wire:model="address.street"/>
                </div>
            </div>
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                <label for="{{ md5('address.country_id') }}"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Country') }}
                </label>
                <div class="col-span-2">
                    <x-select
                        wire:model="address.country_id"
                        searchable
                        :options="app(\FluxErp\Models\Country::class)->all(['id', 'name'])"
                        option-label="name"
                        option-value="id"
                    ></x-select>
                </div>
            </div>
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                <label for="postal-code"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Zip / City') }}
                </label>
                <div class="mt-1 w-full items-center space-x-2 sm:col-span-2 sm:mt-0 sm:flex sm:space-x-2">
                    <div class="flex-none">
                        <x-input wire:model="address.zip"/>
                    </div>
                    <div class="grow">
                        <x-input wire:model="address.city"/>
                    </div>
                </div>
            </div>
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                <label for="{{ md5('address.language_id') }}"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Language') }}
                </label>
                <div class="col-span-2">
                    <x-select
                        wire:model="address.language_id"
                        searchable
                        :options="app(\FluxErp\Models\Language::class)->all(['id', 'name'])"
                        option-label="name"
                        option-value="id"
                    ></x-select>
                </div>
            </div>
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                <label for="{{ md5('password') }}"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Password') }}
                </label>
                <div class="col-span-2">
                    <x-inputs.password wire:model="loginPassword"/>
                </div>
            </div>
        </form>
        @if(auth()->user()->can('profiles.{id?}.get') && auth()->id() !== ($address['id'] ?? ''))
            <div class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-gray-200 sm:pt-5">
                <label for="{{ md5('address.can_login') }}"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Active') }}
                </label>
                <div class="col-span-2">
                    <x-toggle md wire:model="address.can_login"/>
                </div>
            </div>
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                <label for="{{ md5('address.login_name') }}"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Email') }}
                </label>
                <div class="col-span-2">
                    <x-input wire:model="address.login_name"/>
                </div>
            </div>
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                <label for="{{ md5('permissions') }}"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Permissions') }}
                </label>
                <div class="col-span-2 space-y-3">
                    @foreach($permissions as $permission)
                        <x-toggle
                            md
                            wire:model.number="address.permissions"
                            :id="uniqid()"
                            :value="$permission['id']"
                            :label="__($permission['name'])"
                        />
                    @endforeach
                </div>
            </div>
        @endif
        <h3 class="pt-12 text-lg font-medium leading-6 text-gray-900 dark:text-gray-50">
            {{ __('Contact options') }}
        </h3>
        <div
            x-data="{
                contactOptions: $wire.entangle('contactOptions'),
                removeContactOption: function (index, group) {
                    this.contactOptions[group].splice(index, 1);
                },
            }"
            class="space-y-6"
        >
            <template x-for="(contactOptionGroups, key) in contactOptions">
                <div class="dark:bg-secondary-700 rounded-md bg-gray-50 p-3">
                    <h4 class="pl-3 text-lg font-semibold dark:text-gray-50" x-text="key"></h4>
                    <div class="space-y-2">
                        <template x-for="(contactOption, index) in contactOptionGroups" :key="'contactOption' + index">
                            <div class="grid grid-cols-3">
                                <div class="flex items-center">
                                    <div class="flex items-center pr-1.5 transition-all">
                                        <x-button.circle 2xs negative label="-" x-on:click.prevent="removeContactOption(index, key)"></x-button.circle>
                                    </div>
                                    <div class="pr-1.5">
                                        <x-checkbox
                                            x-model="contactOption.is_primary"
                                            x-on:change="contactOptions = _.each(contactOptions, function (option) {
                                            option.is_primary = false;
                                        }); contactOption.is_primary = true"
                                        />
                                    </div>
                                    <x-input
                                        x-model="contactOption.label"
                                    ></x-input>
                                </div>
                                <div class="col-span-2">
                                    <x-input
                                        x-model="contactOption.value"
                                    ></x-input>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="flex space-x-2 pt-5 transition-all">
                        <x-button.circle 2xs positive label="+" x-on:click.prevent="contactOptions[key].push({type: key, label: key, address_id: address.id})" />
                        <div class="text-sm">
                            <span x-text="key"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <x-errors />
        <div class="flex justify-end pt-8">
            <x-button primary :label="__('Save')" wire:click="save" />
        </div>
    </div>
    <div x-show="showUserList">
        <x-portal.profiles></x-portal.profiles>
    </div>
</div>
