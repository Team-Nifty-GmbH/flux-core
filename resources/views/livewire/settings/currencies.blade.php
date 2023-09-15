<div class="py-6" x-data="{currency: @entangle('selectedCurrency')}">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold dark:text-white">{{ __('Currencies') }}</h1>
                <div class="mt-2 text-sm text-gray-300">{{ __('A list of all the currencies') }}</div>
            </div>
        </div>
        @include('tall-datatables::livewire.data-table')
    </div>

    <x-modal.card :title="__('Edit Currency')" wire:model="editModal">
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200">
                <div>
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-6">
                        <div class="space-y-3 sm:col-span-6">
                            <x-input wire:model.live="selectedCurrency.name" :label="__('Currency Name')"/>
                            <x-input wire:model.live="selectedCurrency.iso" :label="__('ISO')"/>
                            <x-input wire:model.live="selectedCurrency.symbol" :label="__('Symbol')"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                <div x-bind:class="currency.id > 0 || 'invisible'">
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
