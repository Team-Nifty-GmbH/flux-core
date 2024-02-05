<div>
    @can('action.contact.create')
        <x-modal :name="'new-contact'">
            <x-card>
                <x-select wire:model="contact.client_id" :options="\FluxErp\Models\Client::all()"
                          label="{{ __('Client') }}" option-label="name" option-value="id"/>
                <div class="space-y-6 sm:space-y-5">
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
                        <label for="{{ md5('address.salutation') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                            {{ __('Salutation') }}
                        </label>
                        <div class="col-span-2">
                            <x-input wire:model="contact.main_address.salutation"/>
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
                                :options="\FluxErp\Models\Country::all(['id', 'name'])"
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
                                :options="\FluxErp\Models\Language::all()"
                                option-label="name"
                                option-value="id"
                            />
                        </div>
                    </div>
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
    @include('tall-datatables::livewire.data-table')
</div>
