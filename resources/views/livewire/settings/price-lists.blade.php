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
                @if(resolve_static(\FluxErp\Actions\PriceList\CreatePriceList::class, 'canPerformAction', [false]))
                    <x-button wire:click="showEditModal()" primary :label="__('New Price List')" />
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
                            <x-select
                                wire:model="selectedPriceList.parent_id"
                                :label="__('Parent')"
                                :options="$priceLists"
                                option-value="id"
                                option-label="name"
                            />
                            <div x-show="priceList.parent_id > 0 " class="grid grid-cols-1 gap-y-6">
                                <x-inputs.number wire:model.number="selectedPriceList.discount.discount" :label="__('Discount')"/>
                                <x-toggle wire:model.boolean="selectedPriceList.discount.is_percentage" lg :label="__('Is Percentage')"/>
                            </div>
                            <x-input wire:model="selectedPriceList.price_list_code" :label="__('Code')"/>
                            <x-toggle wire:model.boolean="selectedPriceList.is_net" lg :label="__('Is Net')"/>
                            <x-toggle wire:model.boolean="selectedPriceList.is_default" lg :label="__('Is Default')"/>
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
                                        <x-inputs.number x-model.number="productCategory.discounts[0].discount"
                                                 :disabled="! (($selectedPriceList['id'] ?? false) ? resolve_static(\FluxErp\Actions\PriceList\UpdatePriceList::class, 'canPerformAction', [false]) : resolve_static(\FluxErp\Actions\Discount\CreateDiscount::class, 'canPerformAction', [false]))"
                                        />
                                    </div>
                                </td>
                                <td>
                                    <div class="flex justify-center">
                                        <x-checkbox
                                            x-model.boolean="productCategory.discounts[0].is_percentage"
                                            :disabled="! (($selectedPriceList['id'] ?? false) ? resolve_static(\FluxErp\Actions\Discount\UpdateDiscount::class, 'canPerformAction', [false]) : resolve_static(\FluxErp\Actions\Discount\CreateDiscount::class, 'canPerformAction', [false]))"
                                        />
                                    </div>
                                <td class="text-right">
                                    @if(($selectedPriceList['id'] ?? false) ? resolve_static(\FluxErp\Actions\Discount\UpdateDiscount::class, 'canPerformAction', [false]) : resolve_static(\FluxErp\Actions\Discount\CreateDiscount::class, 'canPerformAction', [false]))
                                        <x-button icon="trash" negative x-on:click="$wire.removeCategoryDiscount(index)"/>
                                    @endif
                                </td>
                            </tr>
                        </template>
                    </x-table>
                    <div class="flex justify-between mt-4">
                        @if(resolve_static(\FluxErp\Actions\Discount\CreateDiscount::class, 'canPerformAction', [false]) && ($selectedPriceList['id'] ?? false) ? resolve_static(\FluxErp\Actions\PriceList\UpdatePriceList::class, 'canPerformAction', [false]) : resolve_static(\FluxErp\Actions\PriceList\CreatePriceList::class, 'canPerformAction', [false]))
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
                                <x-inputs.number wire:model.number="newCategoryDiscount.discount"/>
                            </div>
                            <div class="mt-2">
                                <x-checkbox
                                    wire:model.boolean="newCategoryDiscount.is_percentage"
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
                    @if(resolve_static(\FluxErp\Actions\PriceList\DeletePriceList::class, 'canPerformAction', [false]))
                        <x-button flat negative :label="__('Delete')" x-on:click="close" wire:click="delete"/>
                    @endif
                </div>
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close"/>
                    @if(($selectedPriceList['id'] ?? false) ? resolve_static(\FluxErp\Actions\PriceList\UpdatePriceList::class, 'canPerformAction', [false]) : resolve_static(\FluxErp\Actions\PriceList\CreatePriceList::class, 'canPerformAction', [false]))
                        <x-button primary :label="__('Save')" wire:click="save"/>
                    @endif
                </div>
            </div>
        </x-slot>
    </x-modal.card>
</div>
