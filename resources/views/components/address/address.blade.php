<div class="table w-full table-auto space-y-3 sm:space-y-3" x-ref="address">
    <div
        class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.company') }}"
               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Company') }}
        </label>
        <div class="col-span-2 w-full">
            <x-input x-bind:readonly="!edit"
                     x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                     wire:model="address.company"/>
        </div>
    </div>
    <div
        class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.salutation') }}"
               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Salutation') }}
        </label>
        <div class="col-span-2 w-full">
            <x-input x-bind:readonly="!edit"
                     x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                     wire:model="address.salutation"></x-input>
        </div>
    </div>
    <div
        class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.title') }}"
               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Title') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!edit"
                     x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                     wire:model="address.title"/>
        </div>
    </div>
    <div
        class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.firstname') }}"
               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Firstname') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!edit"
                     x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                     wire:model="address.firstname"/>
        </div>
    </div>
    <div
        class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.lastname') }}"
               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Lastname') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!edit"
                     x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                     wire:model="address.lastname"/>
        </div>
    </div>
    <div
        class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.street') }}"
               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Street') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!edit"
                     x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                     wire:model="address.street"/>
        </div>
    </div>
    <div
        class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.country_id') }}"
               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Country') }}
        </label>
        <div class="col-span-2">
            <x-select x-bind:readonly="!edit"
                      x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                      wire:model="address.country_id"
                      searchable
                      :options="$countries"
                      option-label="name"
                      option-value="id"></x-select>
        </div>
    </div>
    <div
        class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="postal-code"
               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Zip / City') }}
        </label>
        <div class="mt-1 w-full items-center space-x-2 sm:col-span-2 sm:mt-0 sm:flex sm:space-x-2">
            <div class="flex-none">
                <x-input x-bind:readonly="!edit"
                         x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                         wire:model="address.zip"/>
            </div>
            <div class="grow">
                <x-input x-bind:readonly="!edit"
                         x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                         wire:model="address.city"/>
            </div>
        </div>
    </div>
    <div
            class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.email') }}"
               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Email') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!edit"
                     class="pl-12"
                     x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                     wire:model="address.email">
                <x-slot:prepend>
                    <div class="absolute inset-y-0 left-0 flex items-center p-0.5">
                        <x-button
                                class="h-full rounded-l-md"
                                icon="mail"
                                primary
                                flat
                                squared
                                x-on:click.prevent="window.open('mailto:' + $wire.address.email)"
                        />
                    </div>
                </x-slot:prepend>
            </x-input>
        </div>
    </div>
    <div
            class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.phone') }}"
               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Phone') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!edit"
                     class="pl-12"
                     x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                     wire:model="address.phone">
                <x-slot:prepend>
                    <div class="absolute inset-y-0 left-0 flex items-center p-0.5">
                        <x-button
                                class="h-full rounded-l-md"
                                icon="phone"
                                primary
                                flat
                                squared
                                x-on:click.prevent="window.open('tel:' + $wire.address.phone)"
                        />
                    </div>
                </x-slot:prepend>
            </x-input>
        </div>
    </div>
    <div
            class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.url') }}"
               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('URL') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!edit"
                     class="pl-12"
                     x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                     wire:model="address.url">
                <x-slot:prepend>
                    <div class="absolute inset-y-0 left-0 flex items-center p-0.5">
                        <x-button
                                class="h-full rounded-l-md"
                                icon="globe"
                                primary
                                flat
                                squared
                                x-on:click.prevent="window.open('//' + $wire.address.url)"
                        />
                    </div>
                </x-slot:prepend>
            </x-input>
        </div>
    </div>
    <div
        class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.language_id') }}"
               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Language') }}
        </label>
        <div class="col-span-2">
            <x-select x-bind:disabled="!edit"
                      x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                      wire:model="address.language_id" searchable
                      :options="$languages" option-label="name" option-value="id"></x-select>
        </div>
    </div>
</div>
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
    wire:ignore
>
    <template x-for="(contactOptionGroups, key) in contactOptions">
        <div class="dark:bg-secondary-700 rounded-md bg-gray-50 p-3">
            <h4 class="pl-3 text-lg font-semibold dark:text-gray-50" x-text="key"></h4>
            <div class="space-y-2">
                    <template x-for="(contactOption, index) in contactOptionGroups" :key="'contactOption' + index">
                        <div class="grid grid-cols-3">
                            <div class="flex items-center">
                                <div class="flex items-center pr-1.5 transition-all"  x-bind:class="edit || 'hidden'">
                                    <x-button.circle 2xs negative label="-" x-on:click.prevent="removeContactOption(index, key)"></x-button.circle>
                                </div>
                                <x-input
                                    x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                                    x-bind:readonly="!edit"
                                    x-model="contactOption.label"
                                ></x-input>
                            </div>
                            <div class="col-span-2">
                                <x-input
                                    x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                                    x-bind:readonly="!edit"
                                    x-model="contactOption.value"
                                ></x-input>
                            </div>
                        </div>
                    </template>
            </div>
            <div class="flex space-x-2 pt-5 transition-all" x-bind:class="edit || 'hidden'">
                <x-button.circle 2xs positive label="+" x-on:click.prevent="contactOptions[key].push({type: key, label: key, address_id: address.id})" />
                <div class="text-sm">
                    <span x-text="key"></span>
                </div>
            </div>
        </div>
    </template>
</div>
