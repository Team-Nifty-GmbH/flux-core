<div class="py-6" x-data="{orderTypes: @entangle('orderTypes').defer}">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold">{{ __('Order Types') }}</h1>
                <div class="mt-2 text-sm text-gray-300">{{ __('A list of all the order types') }}</div>
            </div>
        </div>
        <div class="mt-8 flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                            <tr class="divide-x divide-gray-200">
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    {{ __('Name') }}
                                </th>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    {{ __('Description') }}
                                </th>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    {{ __('Order Type Enum') }}
                                </th>
                                <th scope="col" class="py-2 pl-2 pr-2 text-left text-sm font-semibold text-gray-900">
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                            <template x-for="(orderType, index) in orderTypes" :key="index">
                                <tr class="divide-x divide-gray-200">
                                    <td x-text="orderType.name.en" class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6">
                                    </td>
                                    <td x-text="orderType.description.en" class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6">
                                    </td>
                                    <td x-text="orderType.order_type_enum" class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6">
                                    </td>
                                    <td class="whitespace-nowrap py-2 pl-2 pr-2 text-center text-sm text-gray-500">
                                        <button x-on:click="$wire.showEditModal(orderType.id)" type="button"
                                                class="inline-flex items-center rounded border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal.card :title="__('Edit Order Type')" wire:model.defer="editModal">
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200">
                <div>
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-6">
                        <div class="space-y-3 sm:col-span-6">
                            <x-input wire:model="selectedOrderType.name" :label="__('Order Type Name')"/>
                            <x-textarea wire:model="selectedOrderType.description" :label="__('Description')"/>
                            <x-select label="{{ __('Order Type Enum') }}" placeholder="{{ __('Select Order Type Enum') }}" wire:model.defer="selectedOrderType.order_type_enum">
                                <x-select.option label="{{ __('Order') }}" value="order" />
                                <x-select.option label="{{ __('Split Order') }}" value="split-order" />
                                <x-select.option label="{{ __('Retoure') }}" value="retoure" />
                            </x-select>

                            <x-checkbox wire:model="selectedOrderType.is_active" :label="__('Is Active')"/>
                            <x-checkbox wire:model="selectedOrderType.is_hidden" :label="__('Is Hidden')"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                <div x-bind:class="orderType.id > 0 || 'invisible'">
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
