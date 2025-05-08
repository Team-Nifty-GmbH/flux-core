<div>
    <x-modal
        id="{{ $commissionRate->modalName() }}"
        z-index="z-30"
        :title="$commissionRate->id ? __('Edit Commission Rate') : __('Create Commission Rate')"
    >
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200">
                <div>
                    <div
                        class="mt-6 grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-6"
                    >
                        <div class="sm:col-span-6" x-show="!$wire.userId">
                            <x-select.styled
                                :label="__('Commission Agent')"
                                wire:model="commissionRate.user_id"
                                :disabled="$commissionRate->id"
                                required
                                select="label:label|value:id"
                                unfiltered
                                :request="[
                                    'url' => route('search', \FluxErp\Models\User::class),
                                    'method' => 'POST',
                                    'params' => [
                                        'with' => 'media',
                                    ],
                                ]"
                            />
                        </div>
                        <div class="sm:col-span-6">
                            <x-select.styled
                                :label="__('Category')"
                                wire:model.live="commissionRate.category_id"
                                select="label:label|value:id"
                                unfiltered
                                :request="[
                                    'url' => route('search', \FluxErp\Models\Category::class),
                                    'method' => 'POST',
                                    'params' => [
                                        'where' => [
                                            [
                                                'column' => 'model_type',
                                                'operator' => '=',
                                                'value' => morph_alias(\FluxErp\Models\Product::class),
                                            ],
                                        ],
                                    ],
                                ]"
                            />
                        </div>
                        <div class="sm:col-span-6">
                            <x-select.styled
                                :label="__('Product')"
                                wire:model.live="commissionRate.product_id"
                                select="label:label|value:id|description:product_number"
                                unfiltered
                                :request="[
                                    'url' => route('search', \FluxErp\Models\Product::class),
                                    'method' => 'POST',
                                    'params' => [
                                        'fields' => [
                                            'id',
                                            'name',
                                            'product_number',
                                        ],
                                        'with' => 'media',
                                    ],
                                ]"
                            />
                        </div>
                        <div class="sm:col-span-6">
                            <x-number
                                :label="__('Commission Rate (in %)')"
                                :placeholder="__('Commission Rate')"
                                wire:model="commissionRate.commission_rate"
                                step="0.01"
                                min="0.01"
                                max="99.99"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('{{ $commissionRate->modalName() }}')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                wire:click="save().then((success) => { if(success) $modalClose('{{ $commissionRate->modalName() }}')})"
            />
        </x-slot>
    </x-modal>
</div>
