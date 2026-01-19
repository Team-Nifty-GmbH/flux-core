<div class="py-6">
    <x-modal id="edit-price-list-modal" :title="__('Price List')">
        <div class="flex flex-col gap-8">
            <div class="flex flex-col gap-4">
                <x-input wire:model="priceList.name" :label="__('Name')" />
                <x-select.styled
                    wire:model="priceList.parent_id"
                    :label="__('Parent')"
                    select="label:name|value:id"
                    :options="$priceLists"
                    searchable
                />
                <div
                    x-cloak
                    x-show="$wire.priceList.parent_id > 0"
                    class="flex flex-col gap-4"
                >
                    <x-number
                        wire:model.number="priceList.discount.discount"
                        :label="__('Discount')"
                    />
                    <x-toggle
                        wire:model.boolean="priceList.discount.is_percentage"
                        :label="__('Is Percentage')"
                    />
                </div>
                <x-input
                    wire:model="priceList.price_list_code"
                    :label="__('Code')"
                />
                <div class="flex flex-col gap-4">
                    <x-toggle
                        wire:model.boolean="priceList.is_net"
                        :label="__('Is Net')"
                    />
                    <x-toggle
                        wire:model.boolean="priceList.is_default"
                        :label="__('Is Default')"
                    />
                    <x-toggle
                        wire:model.boolean="priceList.is_purchase"
                        :label="__('Is Purchase')"
                    />
                </div>
            </div>

            <div
                class="flex flex-col gap-4 border-t border-gray-200 pt-6 dark:border-gray-700"
            >
                <h2
                    class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                >
                    {{ __('Rounding') }}
                </h2>
                <x-select.styled
                    wire:model="priceList.rounding_method_enum"
                    :label="__('Rounding Method')"
                    :options="$roundingMethods"
                />
                <div
                    x-cloak
                    x-show="$wire.priceList.rounding_method_enum !== 'none'"
                >
                    <x-number
                        wire:model.number="priceList.rounding_precision"
                        :label="__('Rounding Precision')"
                    />
                </div>
                <div
                    x-cloak
                    x-show="['nearest', 'end'].includes($wire.priceList.rounding_method_enum)"
                >
                    <x-number
                        wire:model.number="priceList.rounding_number"
                        :label="__('Rounding Number')"
                        step="1"
                        min="0"
                    />
                </div>
                <div
                    x-cloak
                    x-show="['nearest', 'end'].includes($wire.priceList.rounding_method_enum)"
                >
                    <x-select.styled
                        wire:model="priceList.rounding_mode"
                        :label="__('Rounding Mode')"
                        :options="$roundingModes"
                    />
                </div>
            </div>

            <div
                class="flex flex-col gap-4 border-t border-gray-200 pt-6 dark:border-gray-700"
            >
                <h2
                    class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                >
                    {{ __('Product category discounts') }}
                </h2>
                <x-flux::table>
                    <x-slot:header>
                        <th
                            class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100"
                        >
                            {{ __('Category') }}
                        </th>
                        <th
                            class="w-40 px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100"
                        >
                            {{ __('Discount') }}
                        </th>
                        <th
                            class="w-28 px-3 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100"
                        >
                            {{ __('Is Percentage') }}
                        </th>
                        <th class="w-16 px-3 py-3"></th>
                    </x-slot>
                    <template
                        x-for="(category, index) in $wire.discountedCategories"
                        x-bind:key="category.id"
                    >
                        <x-flux::table.row>
                            <td
                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-50"
                                x-text="category.name"
                            ></td>
                            <td class="whitespace-nowrap px-3 py-4">
                                <x-number
                                    x-model.number="category.discounts[0].discount"
                                    :disabled="! ($priceList->id ? resolve_static(\FluxErp\Actions\PriceList\UpdatePriceList::class, 'canPerformAction', [false]) : resolve_static(\FluxErp\Actions\Discount\CreateDiscount::class, 'canPerformAction', [false]))"
                                />
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-center">
                                <x-toggle
                                    x-model.boolean="category.discounts[0].is_percentage"
                                    :disabled="! ($priceList->id ? resolve_static(\FluxErp\Actions\Discount\UpdateDiscount::class, 'canPerformAction', [false]) : resolve_static(\FluxErp\Actions\Discount\CreateDiscount::class, 'canPerformAction', [false]))"
                                />
                            </td>
                            <td class="whitespace-nowrap px-3 py-4">
                                @if ($priceList->id ? resolve_static(\FluxErp\Actions\Discount\UpdateDiscount::class, 'canPerformAction', [false]) : resolve_static(\FluxErp\Actions\Discount\CreateDiscount::class, 'canPerformAction', [false]))
                                    <x-button
                                        icon="trash"
                                        color="red"
                                        flat
                                        x-on:click="$wire.removeCategoryDiscount(index)"
                                    />
                                @endif
                            </td>
                        </x-flux::table.row>
                    </template>
                </x-flux::table>

                @if (resolve_static(\FluxErp\Actions\Discount\CreateDiscount::class, 'canPerformAction', [false]) && $priceList->id ? resolve_static(\FluxErp\Actions\PriceList\UpdatePriceList::class, 'canPerformAction', [false]) : resolve_static(\FluxErp\Actions\PriceList\CreatePriceList::class, 'canPerformAction', [false]))
                    <div class="flex items-end gap-4">
                        <div class="min-w-0 flex-1">
                            <x-select.styled
                                wire:model="newCategoryDiscount.category_id"
                                :label="__('Category')"
                                select="label:label|value:id"
                                unfiltered
                                :request="[
                                    'url' => route('search', \FluxErp\Models\Category::class),
                                    'method' => 'POST',
                                    'params' => [
                                        'where' => [
                                            ['model_type', '=', morph_alias(\FluxErp\Models\Product::class)],
                                        ],
                                    ],
                                ]"
                            />
                        </div>
                        <div class="w-28 shrink-0">
                            <x-number
                                wire:model.number="newCategoryDiscount.discount"
                                :label="__('Discount')"
                            />
                        </div>
                        <div class="shrink-0 pb-0.5">
                            <x-toggle
                                wire:model.boolean="newCategoryDiscount.is_percentage"
                                :label="__('%')"
                            />
                        </div>
                        <div class="shrink-0">
                            <x-button
                                color="primary"
                                icon="plus"
                                wire:click="addCategoryDiscount"
                            />
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <x-slot:footer>
            <x-button
                color="secondary"
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('edit-price-list-modal')"
            />
            <x-button
                color="primary"
                :text="__('Save')"
                wire:click="save().then((success) => { if(success) $modalClose('edit-price-list-modal')})"
            />
        </x-slot>
    </x-modal>
</div>
