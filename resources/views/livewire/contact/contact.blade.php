<div id="contact" class="min-h-full" wire:loading.class="opacity-60">
    <main class="py-10">
        <x-modal.card z-index="z-30" title="{{ __('New contact') }}" blur wire:model.defer="newContactModal">
            <x-errors />
            <div x-data="{newContact: @entangle('newContact').defer}">
                <x-select wire:model.defer="newContact.client_id" :options="\FluxErp\Models\Client::all()"
                          label="{{ __('Client') }}" option-label="name" option-value="id"/>
                <div class="space-y-6 sm:space-y-5">
                    <div
                        class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-gray-200 sm:pt-5">
                        <label for="{{ md5('newContact.address.company') }}"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                            {{ __('Company') }}
                        </label>
                        <div class="col-span-2">
                            <x-input
                                     wire:model.defer="newContact.address.company"/>
                        </div>
                    </div>
                    <div
                        class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                        <label for="{{ md5('address.salutation') }}"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                            {{ __('Salutation') }}
                        </label>
                        <div class="col-span-2">
                            <x-input
                                     wire:model.defer="newContact.address.salutation"/>
                        </div>
                    </div>
                    <div
                        class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                        <label for="{{ md5('address.title') }}"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                            {{ __('Title') }}
                        </label>
                        <div class="col-span-2">
                            <x-input
                                     wire:model.defer="newContact.address.title"/>
                        </div>
                    </div>
                    <div
                        class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                        <label for="{{ md5('address.firstname') }}"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                            {{ __('Firstname') }}
                        </label>
                        <div class="col-span-2">
                            <x-input
                                     wire:model.defer="newContact.address.firstname"/>
                        </div>
                    </div>
                    <div
                        class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                        <label for="{{ md5('address.lastname') }}"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                            {{ __('Lastname') }}
                        </label>
                        <div class="col-span-2">
                            <x-input
                                     wire:model.defer="newContact.address.lastname"/>
                        </div>
                    </div>
                    <div
                        class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                        <label for="{{ md5('address.street') }}"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                            {{ __('Street') }}
                        </label>
                        <div class="col-span-2">
                            <x-input
                                     wire:model.defer="newContact.address.street"/>
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
                                      wire:model.defer="newContact.address.country_id" searchable
                                      :options="\FluxErp\Models\Country::all(['id', 'name'])" option-label="name"
                                      option-value="id"></x-select>
                        </div>
                    </div>
                    <div
                        class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
                        <label for="postal-code"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                            {{ __('Zip / City') }}
                        </label>
                        <div class="mt-1 w-full items-center space-y-2 sm:col-span-2 sm:mt-0 sm:flex sm:space-x-2">
                            <div class="flex-none">
                                <x-input
                                         wire:model.defer="newContact.address.zip"/>
                            </div>
                            <div class="grow">
                                <x-input
                                         wire:model.defer="newContact.address.city"/>
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
                                      wire:model.defer="newContact.address.language_id" searchable
                                      :options="\FluxErp\Models\Language::all()" option-label="name" option-value="id"></x-select>
                        </div>
                    </div>
                </div>
            </div>
            <x-slot name="footer">
                <div class="flex justify-end gap-x-4">
                    <x-button flat label="{{ __('Cancel') }}" x-on:click="close"/>
                    <x-button primary label="{{ __('Save') }}" wire:click="save"/>
                </div>
            </x-slot>
        </x-modal.card>
        <!-- Page header -->
        <div
            class="mx-auto px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:px-8">
            <div class="flex items-center space-x-5">
                <label for="avatar" style="cursor: pointer">
                    <x-avatar xl src="{{ $avatar }}" />
                </label>
                <input type="file" accept="image/*" id="avatar" class="hidden" wire:model="avatar"/>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                        <div
                            class="opacity-40 transition-opacity hover:opacity-100">{{ $contact['customer_number'] }}</div>
                        {{ implode(', ', array_filter([$contact['main_address']['company'], trim($contact['main_address']['firstname'] . ' ' . $contact['main_address']['lastname'])], function($value) {return $value !== '';})) }}
                    </h1>
                </div>
            </div>
            <div class="justify-stretch mt-6 flex flex-col-reverse space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
                @can('api.contacts.{id}.delete')
                    <x-button negative label="{{ __('Delete') }}" x-on:click="
                        window.$wireui.confirmDialog({
                        title: '{{ __('Delete contact') }}',
                        description: '{{ __('Do you really want to delete this contact?') }}',
                        icon: 'error',
                        accept: {
                            label: '{{ __('Delete') }}',
                            method: 'delete',
                        },
                        reject: {
                            label: '{{ __('Cancel') }}',
                        }
                        }, '{{ $this->id }}')
                    "/>
                @endcan
                @can('api.contacts.post')
                    <x-button primary label="{{ __('New') }}" x-on:click="$openModal('newContactModal')"/>
                @endcan
            </div>
        </div>
        <x-tabs
            wire:model="tab"
            :tabs="$tabs"
            wire:ignore
        >
            <div class="w-full lg:col-start-1 xl:col-span-2 xl:flex xl:space-x-6">
                <section class="w-full lg:pt-0">
                    <x-errors />
                    <x-spinner />
                    <x-dynamic-component :component="'contact.' . $tab" />
                </section>
            </div>
        </x-tabs>
    </main>
</div>
