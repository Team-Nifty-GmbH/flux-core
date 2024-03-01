<div class="py-6" x-data="{country: @entangle('selectedCountry').live}">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold dark:text-white">{{ __('Countries') }}</h1>
                <div class="mt-2 text-sm text-gray-300">{{ __('A list of all the countries') }}</div>
            </div>
        </div>
        @include('tall-datatables::livewire.data-table')
    </div>

    <x-modal.card :title="$selectedCountry['id'] ?? false ? __('Edit Country') : __('Create Country')" wire:model="editModal">
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200">
                <div>
                    <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="space-y-3 sm:col-span-6">
                            <x-input wire:model="selectedCountry.name" :label="__('Country Name')"/>
                            <x-select wire:model="selectedCountry.language_id"
                                      :label="__('Language')"
                                      :options="app(\FluxErp\Models\Language::class)->all(['id', 'name'])->toArray()"
                                      option-value="id"
                                      option-label="name"
                            />
                            <x-select wire:model="selectedCountry.currency_id"
                                      :label="__('Currency')"
                                      :options="app(\FluxErp\Models\Currency::class)->all(['id', 'name'])->toArray()"
                                      option-value="id"
                                      option-label="name"
                            />
                            <x-input wire:model="selectedCountry.iso_alpha2" :label="__('ISO alpha2')"/>
                            <x-input wire:model="selectedCountry.iso_alpha3" :label="__('ISO alpha3')"/>
                            <x-input wire:model="selectedCountry.iso_numeric" :label="__('ISO numeric')"/>
                            <x-toggle wire:model="selectedCountry.is_active" lg :label="__('Active')"/>
                            <x-toggle wire:model="selectedCountry.is_default" lg :label="__('Default')"/>
                            <x-toggle wire:model="selectedCountry.is_eu_country" lg :label="__('EU Country')"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                <div x-bind:class="country.id > 0 || 'invisible'">
                    <x-button flat negative :label="__('Delete')" x-on:click="close" wire:click="delete"/>
                </div>
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close"/>
                    <x-button primary :label="__('Save')" wire:click="save"/>
                </div>
            </div>
        </x-slot>
    </x-modal.card>
</div>
