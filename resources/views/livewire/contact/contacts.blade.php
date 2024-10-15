@use('\FluxErp\Enums\SalutationEnum')
<div>
    @can('action.contact.create')
        <x-modal :name="'new-contact'">
            <x-card>
                <x-select wire:model="contact.client_id"
                          label="{{ __('Client') }}"
                          :options="resolve_static(\FluxErp\Models\Client::class, 'query')->get(['id', 'name'])"
                          option-label="name"
                          option-value="id"
                />
                <div class="space-y-6 sm:space-y-5">
                    @section('contact')
                        <div
                            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-gray-200 sm:pt-5">
                            <label for="{{ md5('contact.company') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                                {{ __('Company') }}
                            </label>
                            <div class="col-span-2">
                                <x-input x-ref="company" wire:model="contact.main_address.company"/>
                            </div>
                        </div>
                        <div
                            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-label :label="__('Salutation')" for="{{ md5('contact.main_address.salutation') }}" />
                            <div class="col-span-2 w-full">
                                <x-select
                                    :options="SalutationEnum::valuesLocalized()"
                                    option-key-value
                                    x-bind:readonly="!$wire.edit"
                                    wire:model="contact.main_address.salutation"
                                />
                            </div>
                        </div>
                        <div
                            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                            <label for="{{ md5('address.title') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                                {{ __('Title') }}
                            </label>
                            <div class="col-span-2">
                                <x-input wire:model="contact.main_address.title"/>
                            </div>
                        </div>
                        <div
                            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                            <label for="{{ md5('address.firstname') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                                {{ __('Firstname') }}
                            </label>
                            <div class="col-span-2">
                                <x-input wire:model="contact.main_address.firstname"/>
                            </div>
                        </div>
                        <div
                            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                            <label for="{{ md5('address.lastname') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                                {{ __('Lastname') }}
                            </label>
                            <div class="col-span-2">
                                <x-input wire:model="contact.main_address.lastname"/>
                            </div>
                        </div>
                    @show
                    @section('address')
                        <div
                            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                            <label for="{{ md5('address.street') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                                {{ __('Street') }}
                            </label>
                            <div class="col-span-2">
                                <x-input wire:model="contact.main_address.street"/>
                            </div>
                        </div>
                        <div
                            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                            <label for="postal-code" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                                {{ __('Zip / City') }}
                            </label>
                            <div class="mt-1 w-full items-center space-x-2 sm:col-span-2 sm:mt-0 sm:flex sm:space-x-2">
                                <div class="flex-none">
                                    <x-input wire:model="contact.main_address.zip"/>
                                </div>
                                <div class="grow">
                                    <x-input wire:model="contact.main_address.city"/>
                                </div>
                            </div>
                        </div>
                        <div class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                            <label for="{{ md5('address.countryId') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                                {{ __('Country') }}
                            </label>
                            <div class="col-span-2">
                                <x-select
                                    wire:model="contact.main_address.country_id"
                                    searchable
                                    :options="resolve_static(\FluxErp\Models\Country::class, 'query')->get(['id', 'name'])"
                                    option-label="name"
                                    option-value="id"
                                />
                            </div>
                        </div>
                        <div
                            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                            <label for="{{ md5('address.language_id') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                                {{ __('Language') }}
                            </label>
                            <div class="col-span-2">
                                <x-select
                                    wire:model="contact.main_address.language_id"
                                    searchable
                                    :options="resolve_static(\FluxErp\Models\Language::class, 'query')->get(['id', 'name'])"
                                    option-label="name"
                                    option-value="id"
                                />
                            </div>
                        </div>
                    @show
                    @section('contact-channels')
                        <div
                            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                            <label for="{{ md5('contact.main_address.email_primary') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                                {{ __('Email') }}
                            </label>
                            <div class="col-span-2">
                                <x-input x-bind:readonly="!$wire.edit" wire:model="contact.main_address.email_primary" />
                            </div>
                        </div>
                        <div
                            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                            <label for="{{ md5('contact.main_address.phone') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                                {{ __('Phone') }}
                            </label>
                            <div class="col-span-2">
                                <x-input x-bind:readonly="!$wire.edit" wire:model="contact.main_address.phone" />
                            </div>
                        </div>
                    @show
                    @section('contact-origin')
                        <div
                            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                            <label for="{{ md5('contact.contact_origin_id') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                                {{ __('Contact Origin') }}
                            </label>
                            <div class="col-span-2">
                                <x-select
                                    wire:model="contact.contact_origin_id"
                                    searchable
                                    :options="resolve_static(\FluxErp\Models\ContactOrigin::class, 'query')->where('is_active', true)->get(['id', 'name'])"
                                    option-label="name"
                                    option-value="id"
                                />
                            </div>
                        </div>
                    @show
                </div>
                <x-slot name="footer">
                    <div class="flex justify-end gap-x-4">
                        <x-button flat label="{{ __('Cancel') }}" x-on:click="close"/>
                        <x-button primary label="{{ __('Save') }}" wire:click="save"/>
                    </div>
                </x-slot>
            </x-card>
        </x-modal>
    @endcan
    <div>
        <div
            x-on:load-map.window="$nextTick(() => onChange())"
            class="py-4 z-0"
            x-data="addressMap($wire, 'loadMap', false, '{{ auth()->user()?->getAvatarUrl() }}')"
            x-cloak
            x-show="$wire.showMap"
            x-collapse
        >
            <x-card class="w-full">
                <x-slot:action>
                    <x-button.circle wire:click="$set('showMap', false, true)" icon="x" />
                </x-slot:action>
                <div x-intersect.once="onChange()">
                    <div id="map" class="h-96 min-w-96"></div>
                </div>
            </x-card>
        </div>
    </div>
</div>
