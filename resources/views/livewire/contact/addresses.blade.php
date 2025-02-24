<div class="flex flex-col md:flex-row gap-4">
    <div class="flex flex-col gap-6" wire:ignore>
        @section('left-side-bar')
            <div class="min-w-96 overflow-auto max-h-56 md:max-h-none">
                <x-card :title="__('Addresses')">
                    @canAction(\FluxErp\Actions\Address\CreateAddress::class)
                        <x-slot:header>
                            <x-button
                                wire:click="new()"
                                color="indigo"
                                :text="__('New')"
                            />
                        </x-slot:header>
                    @endCanAction
                    <div class="flex flex-col gap-1.5">
                        @section('left-side-bar.address-list')
                            <template x-for="addressItem in $wire.addresses">
                                <div
                                    wire:click="select(addressItem.id)"
                                    x-bind:class="$wire.address.id === addressItem.id && 'rounded-lg ring-2 ring-inset ring-primary-500 bg-blue-100 dark:bg-secondary-700'"
                                    class="dark:hover:bg-secondary-800 cursor-pointer space-y-2 p-1.5 hover:bg-blue-50"
                                >
                                    <div class="flex w-full justify-between gap-1.5 dark:text-gray-50" x-bind:class="! addressItem.is_active && 'text-secondary-400 dark:text-gray-200'">
                                        <div class="text-sm text-ellipsis whitespace-nowrap">
                                            @section('left-side-bar.address-list.address')
                                                <div class="font-semibold" x-text="addressItem.company"></div>
                                                <div x-text="((addressItem.firstname || '') + ' ' + (addressItem.lastname || '')).trim()"></div>
                                                <div x-text="addressItem.street"></div>
                                                <div x-text="((addressItem.zip || '') + ' ' + (addressItem.city || '')).trim()"></div>
                                            @show
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            @section('left-side-bar.address-list.badges')
                                                <x-badge x-show="addressItem.is_main_address" x-cloak color="emerald" :text="__('Main Address')" />
                                                <x-badge x-show="addressItem.is_delivery_address" x-cloak color="indigo" :text="__('Delivery Address')" />
                                                <x-badge x-show="addressItem.is_invoice_address" x-cloak color="amber" :text="__('Invoice Address')" />
                                            @show
                                        </div>
                                    </div>
                                </div>
                            </template>
                        @show
                    </div>
                </x-card>
            </div>
            @section('left-lide-bar.contact-attributes')
                <x-card>
                    <div class="flex flex-col gap-1.5" x-bind:class="! $wire.$parent.edit && 'pointer-events-none'">
                        <x-select.styled
                            multiple
                            x-bind:disabled="! $wire.$parent.edit"
                            wire:model.number="contact.categories"
                            :label="__('Categories')"
                            option-description="description"
                            :request="[
                                'url' => route('search', \FluxErp\Models\Category::class),
                                'method' => 'POST',
                                'params' => [
                                    'where' => [
                                        [
                                            'model_type',
                                            '=',
                                            morph_alias(\FluxErp\Models\Contact::class),
                                        ],
                                    ],
                                ],
                            ]"
                        />
                        <x-select.styled
                            multiple
                            x-bind:disabled="! $wire.$parent.edit"
                            wire:model.number="contact.industries"
                            :label="__('Industries')"
                            select="label:name|value:id"
                            :request="[
                                'url' => route('search', \FluxErp\Models\Industry::class),
                                'method' => 'POST',
                                'params' => [
                                    'searchFields' => ['name'],
                                ],
                            ]"
                        />
                        <x-select.styled
                            searchable
                            x-bind:disabled="! $wire.$parent.edit"
                            wire:model.number="contact.contact_origin_id"
                            :label="__('Contact Origin')"
                            option-key-value
                            :options="$contactOrigins"
                        />
                    </div>
                </x-card>
            @show
            @section('left-lide-bar.address-widget')
                <x-card>
                    <livewire:widgets.address
                        lazy
                        :without-header="true"
                        :model-id="$contact->main_address_id"
                    />
                </x-card>
            @show
        @show
    </div>
    <div class="w-full" x-data="{formatter: @js(resolve_static(\FluxErp\Models\Address::class, 'typeScriptAttributes'))}">
        <x-card :title="__('Details')">
            <x-slot:header>
                @section('address-details.actions')
                    <div class="flex gap-1.5">
                        @canAction(\FluxErp\Actions\Address\UpdateAddress::class)
                            <div x-cloak x-show="$wire.edit">
                                <x-button color="secondary" light
                                    x-on:click="$wire.edit = false; $wire.reloadAddress()"
                                    :text="__('Cancel')"
                                />
                                <x-button
                                    x-on:click="$wire.save()"
                                    color="indigo"
                                    :text="__('Save')"
                                />
                            </div>
                            <div x-cloak x-show="! $wire.edit">
                                <x-button
                                    color="secondary"
                                    light
                                    wire:click="replicate()"
                                    :text="__('Duplicate')"
                                />
                            </div>
                            <div x-cloak x-show="! $wire.edit">
                                <x-button
                                    x-on:click="$wire.edit = true;"
                                    color="indigo"
                                    :text="__('Edit')"
                                />
                            </div>
                        @endCanAction
                        @canAction(\FluxErp\Actions\Address\DeleteAddress::class)
                            <div x-cloak x-show="! $wire.address.is_main_address">
                                <x-button
                                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Address')]) }}"
                                    wire:click="delete()"
                                    color="red"
                                    :text="__('Delete')"
                                />
                            </div>
                        @endCanAction
                    </div>
                @show
            </x-slot:header>
            <x-flux::tabs
                wire:model.live="tab"
                :$tabs
                wire:ignore
            />
        </x-card>
    </div>
</div>
