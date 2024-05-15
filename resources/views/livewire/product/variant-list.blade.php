<div x-data="{productOptionGroup: null}">
    <x-modal name="generate-variants-modal" max-width="6xl">
        <x-card :title="__('Edit Variants')">
            <div x-transition x-show="! Object.values($wire.variants).length > 0">
                <div class="flex gap-4" x-on:data-table-row-clicked="$wire.loadOptions($event.detail.id ?? $event.detail.record.id); productOptionGroup = $event.detail.record ?? $event.detail;">
                    <div class="flex-grow">
                        <livewire:product.product-option-group-list />
                    </div>
                    <div x-collapse x-show="Object.values($wire.productOptions).length > 0" class="w-1/2">
                        <x-card>
                            <x-slot:title>
                                <span x-text="productOptionGroup?.name"></span>
                            </x-slot:title>
                            <template x-for="productOption in $wire.productOptions" :key="productOption.id">
                                <div class="flex gap-1.5">
                                    <x-checkbox
                                        x-bind:id="'product-option' + productOption.id"
                                        x-bind:value="productOption.id"
                                        x-model.number="$wire.selectedOptions[productOption.product_option_group_id]"
                                    />
                                    <label
                                        x-text="productOption.name"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-50"
                                        x-bind:for="'product-option' + productOption.id"
                                    >
                                    </label>
                                </div>
                            </template>
                        </x-card>
                    </div>
                </div>
            </div>
            <div x-transition x-show="Object.values($wire.variants).length > 0">
                <div>
                    <div>
                        <span class="font-bold" x-text="$wire.variants?.new?.length ?? 0"></span> <span>{{ __('Variants will be created.') }}</span>
                    </div>
                    <div>
                        <span class="font-bold" x-text="$wire.variants?.delete?.length ?? 0"></span> <span>{{ __('Variants will be deleted.') }}</span>
                    </div>
                    <div>
                        <span class="font-bold" x-text="$wire.variants?.existing?.length ?? 0"></span> <span>{{ __('Variants already exist.') }}</span>
                    </div>
                </div>
            </div>
            <x-slot:footer>
                <div class="flex justify-end gap-1.5">
                    <x-button
                        x-show="! Object.values($wire.variants).length > 0"
                        flat
                        :label="__('Cancel')"
                        x-on:click="close()"
                    />
                    <x-button
                        x-show="! Object.values($wire.variants).length > 0"
                        primary
                        spinner="next()"
                        :label="__('Next')"
                        wire:click="next()"
                    />
                    <x-button
                        x-show="Object.values($wire.variants).length > 0"
                        x-cloak
                        flat
                        :label="__('Back')"
                        x-on:click="$wire.variants = {}"
                    />
                    <x-button
                        x-show="Object.values($wire.variants).length > 0"
                        x-cloak
                        primary
                        spinner="save()"
                        :label="__('Save')"
                        wire:flux-confirm.icon.error="{{ __('Save Variants') }}|{{ __('Non existing product option combinations will be deleted!') }}|{{ __('Cancel') }}|{{ __('OK') }}"
                        wire:click="save().then(() => { close(); })"
                    />
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <div wire:ignore>
        @include('tall-datatables::livewire.data-table')
    </div>
</div>
