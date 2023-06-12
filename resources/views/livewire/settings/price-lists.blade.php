<div class="py-6" x-data="{priceList: @entangle('selectedPriceList'), countries: @entangle('priceLists').defer}">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold">{{ __('Price Lists') }}</h1>
                <div class="mt-2 text-sm text-gray-300">{{ __('A list of all the price lists') }}</div>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                @if(user_can('api.priceLists.post'))
                    <button wire:click="showEditModal()"
                            type="button"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                        {{ __('Add Price List') }}
                    </button>
                @endif
            </div>
        </div>
        <div class="mt-8 flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                        <livewire:data-tables.price-list-list />
                </div>
            </div>
        </div>
    </div>

    <x-modal.card :title="__('Edit PriceList')" wire:model.defer="editModal">
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200">
                <div>
                    <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="space-y-3 sm:col-span-6">
                            <x-input wire:model="selectedPriceList.name" :label="__('Name')"/>
                            <x-input wire:model="selectedPriceList.parent_id" :label="__('Parent')"/>
                            <x-input wire:model="selectedPriceList.price_list_code" :label="__('Code')"/>
                            <x-toggle wire:model="selectedPriceList.is_net" lg :label="__('Is Net')"/>
                            <x-toggle wire:model="selectedPriceList.is_default" lg :label="__('Is Default')"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                <div x-bind:class="priceList.id > 0 || 'invisible'">
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
