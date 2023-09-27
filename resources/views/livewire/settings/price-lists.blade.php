<div class="py-6" x-data="{
    priceList: @entangle('selectedPriceList').live,
    productCategories: @entangle('discountedCategories').live,
    newCategoryDiscount: @entangle('newCategoryDiscount').live
}"
>
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold dark:text-white">{{ __('Price Lists') }}</h1>
                <div class="mt-2 text-sm text-gray-300">{{ __('A list of all the price lists') }}</div>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                @if(user_can('action.price-list.create'))
                    <button wire:click="showEditModal()"
                            type="button"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                        {{ __('New Price List') }}
                    </button>
                @endif
            </div>
        </div>
        <div class="mt-8 flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8"
                     x-on:data-table-row-clicked="$wire.showEditModal($event.detail.id)"
                >
                    <livewire:data-tables.price-list-list />
                </div>
            </div>
        </div>
    </div>

    <x-modal.card wire:model="editModal">
        <x-slot name="title" class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            {{ ($selectedPriceList['id'] ?? false) ? __('Edit Price List') : __('New Price List') }}
        </x-slot>
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200">
                <div>
                    <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="space-y-3 sm:col-span-6">
                            <x-input wire:model="selectedPriceList.name" :label="__('Name')"/>
                            <x-select wire:model="selectedPriceList.parent_id" :label="__('Parent')"
                                :options="$priceLists" option-value="id" option-label="name" />
                            <div x-show="priceList.parent_id > 0 " class="grid grid-cols-1 gap-y-6">
                                <x-input wire:model="selectedPriceList.discount.discount" :label="__('Discount')"/>
                                <x-toggle wire:model="selectedPriceList.discount.is_percentage" lg :label="__('Is Percentage')"/>
                            </div>
                            <x-input wire:model="selectedPriceList.price_list_code" :label="__('Code')"/>
                            <x-toggle wire:model="selectedPriceList.is_net" lg :label="__('Is Net')"/>
                            <x-toggle wire:model="selectedPriceList.is_default" lg :label="__('Is Default')"/>
                        </div>
                    </div>
                </div>
                <div>
                    <x-table>
                        <x-slot name="title">
                            <h2 class="pt-4">{{ __('Product category discounts') }}</h2>
                        </x-slot>
                        <x-slot name="header">
                            <th>{{ __('Category') }}</th>
                            <th class="w-44">{{ __('Discount') }}</th>
                            <th class="w-16">{{ __('%') }}</th>
                            <th class="w-14"></th>
                        </x-slot>
                        <template x-for="(productCategory, index) in productCategories">
                            <tr>
                                <td class="text-center">
                                    <div x-text="productCategory.name" class="mr-2"></div>
                                </td>
                                <td>
                                    <div class="flex justify-center">
                                        <x-input x-model="productCategory.discounts[0].discount"
                                                 :disabled="! (($selectedPriceList['id'] ?? false) ? user_can('action.discount.update') : user_can('action.discount.create'))"
                                        />
                                    </div>
                                </td>
                                <td>
                                    <div class="flex justify-center">
                                        <x-checkbox
                                            x-model="productCategory.discounts[0].is_percentage"
                                            :disabled="! (($selectedPriceList['id'] ?? false) ? user_can('action.discount.update') : user_can('action.discount.create'))"
                                        />
                                    </div>
                                <td class="text-right">
                                    @if(($selectedPriceList['id'] ?? false) ? user_can('action.discount.update') : user_can('action.discount.create'))
                                        <x-button icon="trash" negative x-on:click="$wire.removeCategoryDiscount(index)"/>
                                    @endif
                                </td>
                            </tr>
                        </template>
                    </x-table>
                    <div class="flex justify-between mt-4">
                        @if(user_can('action.discount.create') && ($selectedPriceList['id'] ?? false) ? user_can('action.price-list.update') : user_can('action.price-list.create'))
                            <div>
                                <x-select
                                    wire:model="newCategoryDiscount.category_id"
                                    option-value="id"
                                    option-label="name"
                                    :clearable="false"
                                    :options="$categories"
                                />
                            </div>
                            <div>
                                <x-input wire:model="newCategoryDiscount.discount"/>
                            </div>
                            <div class="mt-2">
                                <x-checkbox
                                    wire:model="newCategoryDiscount.is_percentage"
                                />
                            </div>
                            <div class="">
                                <x-button primary icon="plus" wire:click="addCategoryDiscount"/>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                <div x-bind:class="priceList.id > 0 || 'invisible'">
                    @if(user_can('action.price-list.delete'))
                        <x-button flat negative :label="__('Delete')" x-on:click="close" wire:click="delete"/>
                    @endif
                </div>
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close"/>
                    @if(($selectedPriceList['id'] ?? false) ? user_can('action.price-list.update') : user_can('action.price-list.create'))
                        <x-button primary :label="__('Save')" wire:click="save"/>
                    @endif
                </div>
            </div>
        </x-slot>
    </x-modal.card>
</div>
