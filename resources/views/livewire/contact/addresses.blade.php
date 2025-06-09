<div class="flex flex-col gap-4 md:flex-row">
    <div class="flex flex-col gap-6" wire:ignore>
        @section('left-side-bar')
        <div class="max-h-56 min-w-96 overflow-auto md:max-h-none">
            <x-card>
                @canAction(\FluxErp\Actions\Address\CreateAddress::class)
                    <x-slot:header>
                        <div
                            class="flex w-full items-center justify-between gap-4"
                        >
                            <div>{{ __('Addresses') }}</div>
                            <x-button
                                wire:click="new().then(() => $focusOn('address-company'))"
                                color="indigo"
                                :text="__('New')"
                            />
                        </div>
                    </x-slot>
                @endcanAction

                <div class="flex flex-col gap-1.5">
                    @section('left-side-bar.address-list')
                    <template x-for="addressItem in $wire.addresses">
                        <div
                            wire:click="select(addressItem.id)"
                            x-bind:class="
                                $wire.address.id === addressItem.id &&
                                    'rounded-lg ring-2 ring-inset ring-primary-500 bg-blue-100 dark:bg-secondary-700'
                            "
                            class="dark:hover:bg-secondary-800 cursor-pointer space-y-2 p-1.5 hover:bg-blue-50"
                        >
                            <div
                                class="flex w-full justify-between gap-1.5 dark:text-gray-50"
                                x-bind:class="! addressItem.is_active && 'text-secondary-400 dark:text-gray-200'"
                            >
                                <div
                                    class="text-sm text-ellipsis whitespace-nowrap"
                                >
                                    @section('left-side-bar.address-list.address')
                                    <div
                                        class="font-semibold"
                                        x-text="addressItem.company"
                                    ></div>
                                    <div
                                        x-text="((addressItem.firstname || '') + ' ' + (addressItem.lastname || '')).trim()"
                                    ></div>
                                    <div x-text="addressItem.street"></div>
                                    <div
                                        x-text="((addressItem.zip || '') + ' ' + (addressItem.city || '')).trim()"
                                    ></div>
                                    @show
                                </div>
                                <div class="flex flex-col gap-0.5">
                                    @section('left-side-bar.address-list.badges')
                                    <x-badge
                                        x-show="addressItem.is_main_address"
                                        x-cloak
                                        color="emerald"
                                        :text="__('Main Address')"
                                    />
                                    <x-badge
                                        x-show="addressItem.is_delivery_address"
                                        x-cloak
                                        color="indigo"
                                        :text="__('Delivery Address')"
                                    />
                                    <x-badge
                                        x-show="addressItem.is_invoice_address"
                                        x-cloak
                                        color="amber"
                                        :text="__('Invoice Address')"
                                    />
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
            <div
                class="flex flex-col gap-1.5"
                x-bind:class="! $wire.$parent.edit && 'pointer-events-none'"
            >
                <x-select.styled
                    multiple
                    x-bind:disabled="! $wire.$parent.edit"
                    wire:model.number="contact.categories"
                    :label="__('Categories')"
                    select="label:label|value:id"
                    unfiltered
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
                    unfiltered
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
                    select="label:name|value:id"
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
    <div
        class="w-full"
        x-data="{ formatter: @js(resolve_static(\FluxErp\Models\Address::class, 'typeScriptAttributes')) }"
    >
        <x-card>
            <x-slot:header>
                <div class="flex w-full items-center justify-between gap-4">
                    <div>{{ __('Details') }}</div>
                    @section('address-details.actions')
                    <div class="flex gap-2">
                        @canAction(\FluxErp\Actions\Address\UpdateAddress::class)
                            <div
                                x-cloak
                                x-show="$wire.edit"
                                class="flex gap-x-2"
                            >
                                <x-button
                                    color="secondary"
                                    light
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
                                    wire:click="replicate().then(() => $focusOn('address-company'))"
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
                        @endcanAction

                        @canAction(\FluxErp\Actions\Address\DeleteAddress::class)
                            <div
                                x-cloak
                                x-show="! $wire.address.is_main_address"
                            >
                                <x-button
                                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Address')]) }}"
                                    wire:click="delete()"
                                    color="red"
                                    :text="__('Delete')"
                                />
                            </div>
                        @endcanAction
                    </div>
                    @show
                </div>
            </x-slot>
            <x-flux::tabs wire:model.live="tab" :$tabs wire:ignore />
        </x-card>
    </div>
</div>
