<div class="py-6" x-data="{
    productCategories: @entangle('discountedCategories').live,
    newCategoryDiscount: @entangle('newCategoryDiscount').live
}"
>
    <x-modal name="edit-price-list">
        <x-card>
            <x-slot name="title" class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                {{ __('Edit Price List') }}
            </x-slot>
            <div class="space-y-8 divide-y divide-gray-200">
                <div class="space-y-8 divide-y divide-gray-200">
                    <div>
                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="space-y-2.5 sm:col-span-6">
                                <x-input wire:model="priceList.name" :label="__('Name')"/>
                                <x-select
                                    wire:model="priceList.parent_id"
                                    :label="__('Parent')"
                                    :options="$priceLists"
                                    option-value="id"
                                    option-label="name"
                                />
                                <div x-show="$wire.priceList.parent_id > 0" class="grid grid-cols-1 gap-y-6">
                                    <x-number wire:model.number="priceList.discount.discount" :label="__('Discount')"/>
                                    <x-toggle wire:model.boolean="priceList.discount.is_percentage" lg :label="__('Is Percentage')"/>
                                </div>
                                <x-input wire:model="priceList.price_list_code" :label="__('Code')"/>
                                <x-toggle wire:model.boolean="priceList.is_net" lg :label="__('Is Net')"/>
                                <x-toggle wire:model.boolean="priceList.is_default" lg :label="__('Is Default')"/>
                                <x-toggle wire:model.boolean="priceList.is_purchase" lg :label="__('Is Purchase')"/>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2.5 pt-4">
                        <h2>{{ __('Rounding') }}</h2>
                        <x-select
                            wire:model="priceList.rounding_method_enum"
                            :label="__('Rounding Method')"
                            :options="$roundingMethods"
                            option-key-value
                        />
                        <div x-show="$wire.priceList.rounding_method_enum !== 'none'">
                            <x-number
                                wire:model.number="priceList.rounding_precision"
                                :label="__('Rounding Precision')"
                            />
                        </div>
                        <div x-show="['nearest', 'end'].includes($wire.priceList.rounding_method_enum)">
                            <x-number
                                wire:model.number="priceList.rounding_number"
                                :label="__('Rounding Number')"
                                min="0"
                            />
                        </div>
                        <div x-show="['nearest', 'end'].includes($wire.priceList.rounding_method_enum)">
                            <x-select
                                wire:model="priceList.rounding_mode"
                                :label="__('Rounding Mode')"
                                :options="$roundingModes"
                                option-key-value
                            />
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
                                            <x-number x-model.number="productCategory.discounts[0].discount"
                                                     :disabled="! ($priceList->id ? resolve_static(\FluxErp\Actions\PriceList\UpdatePriceList::class, 'canPerformAction', [false]) : resolve_static(\FluxErp\Actions\Discount\CreateDiscount::class, 'canPerformAction', [false]))"
                                            />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex justify-center">
                                            <x-checkbox
                                                x-model.boolean="productCategory.discounts[0].is_percentage"
                                                :disabled="! ($priceList->id ? resolve_static(\FluxErp\Actions\Discount\UpdateDiscount::class, 'canPerformAction', [false]) : resolve_static(\FluxErp\Actions\Discount\CreateDiscount::class, 'canPerformAction', [false]))"
                                            />
                                        </div>
                                    <td class="text-right">
                                        @if($priceList->id ? resolve_static(\FluxErp\Actions\Discount\UpdateDiscount::class, 'canPerformAction', [false]) : resolve_static(\FluxErp\Actions\Discount\CreateDiscount::class, 'canPerformAction', [false]))
                                            <x-button icon="trash" negative x-on:click="$wire.removeCategoryDiscount(index)"/>
                                        @endif
                                    </td>
                                </tr>
                            </template>
                        </x-table>
                        <div class="flex justify-between mt-4">
                            @if(resolve_static(\FluxErp\Actions\Discount\CreateDiscount::class, 'canPerformAction', [false]) && $priceList->id ? resolve_static(\FluxErp\Actions\PriceList\UpdatePriceList::class, 'canPerformAction', [false]) : resolve_static(\FluxErp\Actions\PriceList\CreatePriceList::class, 'canPerformAction', [false]))
                                <div>
                                    <x-select
                                        wire:model="newCategoryDiscount.category_id"
                                        :clearable="false"
                                        option-value="id"
                                        option-label="label"
                                        option-description="description"
                                        :async-data="[
                                            'api' => route('search', \FluxErp\Models\Category::class),
                                            'method' => 'POST',
                                            'params' => [
                                                'where' => [
                                                    [
                                                        'model_type',
                                                        '=',
                                                        morph_alias(\FluxErp\Models\Product::class),
                                                    ]
                                                ],
                                            ],
                                        ]"
                                    />
                                </div>
                                <div>
                                    <x-number wire:model.number="newCategoryDiscount.discount"/>
                                </div>
                                <div class="mt-2">
                                    <x-checkbox wire:model.boolean="newCategoryDiscount.is_percentage"/>
                                </div>
                                <div class="">
                                    <x-button primary icon="plus" wire:click="addCategoryDiscount"/>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <x-slot:footer>
                <div class="flex justify-between gap-x-4">
                    @if(resolve_static(\FluxErp\Actions\PriceList\DeletePriceList::class, 'canPerformAction', [false]))
                        <div x-bind:class="$wire.priceList.id > 0 || 'invisible'">
                            <x-button
                                flat
                                negative
                                :label="__('Delete')"
                                x-on:click="close"
                                wire:click="delete().then((success) => { if(success) close()})"
                                wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Price List')]) }}"
                            />
                        </div>
                    @endif
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close()})"/>
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
</div>
