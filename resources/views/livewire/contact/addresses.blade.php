<div class="flex flex-col md:flex-row gap-4">
    <div class="flex flex-col gap-6" wire:ignore>
        <div class="min-w-96 overflow-auto max-h-56 md:max-h-none">
            <x-card :title="__('Addresses')">
                @if(resolve_static(\FluxErp\Actions\Address\CreateAddress::class, 'canPerformAction', [false]))
                    <x-slot:action>
                        <x-button
                            wire:click="new()"
                            primary
                            :label="__('New')"
                        />
                    </x-slot:action>
                @endif
                <div class="flex flex-col gap-1.5">
                    <template x-for="addressItem in $wire.addresses">
                        <div
                            wire:click="select(addressItem.id)"
                            x-bind:class="$wire.address.id === addressItem.id && 'rounded-lg ring-2 ring-inset ring-primary-500 bg-blue-100 dark:bg-secondary-700'"
                            class="dark:hover:bg-secondary-800 cursor-pointer space-y-2 p-1.5 hover:bg-blue-50"
                        >
                            <div class="flex w-full justify-between gap-1.5">
                                <div class="text-sm dark:text-gray-50 text-ellipsis whitespace-nowrap">
                                    <div class="font-semibold" x-text="addressItem.company"></div>
                                    <div x-text="((addressItem.firstname || '') + ' ' + (addressItem.lastname || '')).trim()"></div>
                                    <div x-text="addressItem.street"></div>
                                    <div x-text="((addressItem.zip || '') + ' ' + (addressItem.city || '')).trim()"></div>
                                </div>
                                <div class="flex flex-col gap-0.5">
                                    <x-badge x-show="addressItem.is_main_address" positive :label="__('Main Address')" />
                                    <x-badge x-show="addressItem.is_delivery_address" primary :label="__('Delivery Address')" />
                                    <x-badge x-show="addressItem.is_invoice_address" warning :label="__('Invoice Address')" />
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </x-card>
        </div>
        <x-card>
            <div class="flex flex-col gap-1.5" x-bind:class="! $wire.$parent.edit && 'pointer-events-none'">
                <x-select
                    multiselect
                    x-bind:disabled="! $wire.$parent.edit"
                    wire:model.number="contact.categories"
                    :label="__('Categories')"
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
                                    app(\FluxErp\Models\Contact::class)->getMorphClass(),
                                ],
                            ],
                        ],
                    ]"
                />
            </div>
        </x-card>
    </div>
    <div class="w-full">
        <x-card :title="__('Details')">
            <x-slot:action>
                <div class="flex gap-1.5">
                    @if(resolve_static(\FluxErp\Actions\Address\UpdateAddress::class, 'canPerformAction', [false]))
                        <div x-cloak x-show="$wire.edit">
                            <x-button
                                x-on:click="$wire.edit = false; $wire.reloadAddress()"
                                :label="__('Cancel')"
                            />
                            <x-button
                                x-on:click="$wire.save()"
                                primary
                                :label="__('Save')"
                            />
                        </div>
                        <div x-cloak x-show="! $wire.edit">
                            <x-button
                                wire:click="replicate()"
                                :label="__('Duplicate')"
                            />
                        </div>
                        <div x-cloak x-show="! $wire.edit">
                            <x-button
                                x-on:click="$wire.edit = true;"
                                primary
                                :label="__('Edit')"
                            />
                        </div>
                    @endif
                    @if(resolve_static(\FluxErp\Actions\Address\DeleteAddress::class, 'canPerformAction', [false]))
                        <div x-cloak x-show="! $wire.address.is_main_address">
                            <x-button
                                wire:confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Address')]) }}"
                                wire:click="delete()"
                                negative
                                :label="__('Delete')"
                            />
                        </div>
                    @endif
                </div>
            </x-slot:action>
            <x-tabs
                wire:model.live="tab"
                :$tabs
                wire:ignore
            />
        </x-card>
    </div>
</div>
