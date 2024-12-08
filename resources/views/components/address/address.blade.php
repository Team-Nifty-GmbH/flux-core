@use('\FluxErp\Enums\SalutationEnum')
@props([
    'onlyPostal' => false,
    'countries' => resolve_static(\FluxErp\Models\Country::class, 'query')->select(['id', 'name'])->pluck('name', 'id')->toArray(),
    'languages' => resolve_static(\FluxErp\Models\Language::class, 'query')->select(['id', 'name'])->pluck('name', 'id')->toArray(),
])
<div class="table w-full table-auto gap-1.5" x-ref="address">
    @section('contact')
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :text="__('Company')" for="{{ md5('address.company') }}" />
            <div class="col-span-2 w-full">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.company"/>
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :text="__('Department')" for="{{ md5('address.department') }}" />
            <div class="col-span-2 w-full">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.department" />
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <div></div>
            <div class="col-span-2 w-full">
                <x-checkbox :label="__('Formal salutation')" x-bind:disabled="!$wire.edit" wire:model="address.has_formal_salutation"/>
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :text="__('Salutation')" for="{{ md5('address.salutation') }}" />
            <div class="col-span-2 w-full">
                <x-select
                    :options="SalutationEnum::valuesLocalized()"
                    option-key-value
                    x-bind:readonly="!$wire.edit"
                    wire:model="address.salutation"
                />
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :text="__('Title')" for="{{ md5('address.title') }}" />
            <div class="col-span-2">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.title" />
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :text="__('Firstname')" for="{{ md5('address.firstname') }}" />
            <div class="col-span-2">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.firstname" />
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :text="__('Lastname')" for="{{ md5('address.lastname') }}" />
            <div class="col-span-2">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.lastname" />
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :text="__('Addition')" for="{{ md5('address.addition') }}" />
            <div class="col-span-2">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.addition" />
            </div>
        </div>
    @show
    @section('address')
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :text="__('Street')" for="{{ md5('address.street') }}" />
            <div class="col-span-2">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.street" />
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :text="__('Country')" for="{{ md5('address.country_id') }}" />
            <div class="col-span-2">
                <x-select x-bind:readonly="!$wire.edit"
                          wire:model="address.country_id"
                          searchable
                          :options="$countries"
                          option-key-value
                />
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :text="__('Zip / City')" for="postal-code" />
            <div class="mt-1 w-full items-center space-x-2 sm:col-span-2 sm:mt-0 sm:flex sm:space-x-2">
                <div class="flex-none">
                    <x-input x-bind:readonly="!$wire.edit" wire:model="address.zip" />
                </div>
                <div class="grow">
                    <x-input x-bind:readonly="!$wire.edit" wire:model="address.city" />
                </div>
            </div>
        </div>
    @show
    @section('contact-channels')
        @if(! $onlyPostal)
            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
                <x-label :text="__('Date Of Birth')" for="{{ md5('address.date_of_birth') }}" />
                <div class="col-span-2">
                    <x-datetime-picker
                        wire:model="address.date_of_birth"
                        :without-time="true"
                        x-bind:disabled="!$wire.edit"
                    />
                </div>
            </div>
        @endif
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :text="__('Email')" for="{{ md5('address.email_primary') }}" />
            <div class="col-span-2">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.email_primary">
                    <x-slot:prepend>
                        <x-button
                            class="h-full rounded-l-md"
                            icon="envelope"
                            primary
                            flat
                            squared
                            x-on:click.prevent="window.open('mailto:' + $wire.address.email_primary)"
                        />
                    </x-slot:prepend>
                </x-input>
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :text="__('Phone')" for="{{ md5('address.phone') }}" />
            <div class="col-span-2">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.phone">
                    <x-slot:prepend>
                        <x-button
                            class="h-full rounded-l-md"
                            icon="phone"
                            primary
                            flat
                            squared
                            x-on:click.prevent="window.open('tel:' + $wire.address.phone)"
                        />
                    </x-slot:prepend>
                </x-input>
            </div>
        </div>
        @if(! $onlyPostal)
            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
                <x-label :text="__('URL')" for="{{ md5('address.url') }}" />
                <div class="col-span-2">
                    <x-input x-bind:readonly="!$wire.edit" wire:model="address.url">
                        <x-slot:prepend>
                            <x-button
                                class="h-full rounded-l-md"
                                icon="globe-alt"
                                primary
                                flat
                                squared
                                x-on:click.prevent="window.open('//' + $wire.address.url)"
                            />
                        </x-slot:prepend>
                    </x-input>
                </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
                <x-label :text="__('Language')" for="{{ md5('address.language_id') }}" />
                <div class="col-span-2">
                    <x-select x-bind:disabled="!$wire.edit"
                              wire:model="address.language_id"
                              searchable
                              :options="$languages"
                              option-key-value
                    />
                </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
                <x-label :text="__('Tags')" for="{{ md5('address.tags') }}" />
                <div class="col-span-2">
                    <x-select
                        multiselect
                        x-bind:disabled="! $wire.edit"
                        wire:model.number="address.tags"
                        option-value="id"
                        option-label="label"
                        :async-data="[
                            'api' => route('search', \FluxErp\Models\Tag::class),
                            'method' => 'POST',
                            'params' => [
                                'option-value' => 'id',
                                'where' => [
                                    [
                                        'type',
                                        '=',
                                        morph_alias(\FluxErp\Models\Address::class),
                                    ],
                                ],
                            ],
                        ]"
                    >
                        <x-slot:beforeOptions>
                            <div class="px-1">
                                <x-button positive full :text="__('Add')" wire:click="addTag($promptValue())" wire:flux-confirm.prompt="{{ __('New Tag') }}||{{ __('Cancel') }}|{{ __('Save') }}" />
                            </div>
                        </x-slot:beforeOptions>
                    </x-select>
                </div>
            </div>
        @endif
    @show
</div>
@section('attributes')
    @if(! $onlyPostal)
        <h3 class="pt-12 text-lg font-medium leading-6 text-gray-900 dark:text-gray-50">
            {{ __('Attributes') }}
        </h3>
        <hr class="py-2" />
        <div class="flex flex-col gap-1.5">
            <x-toggle :text="__('Active')" x-bind:disabled="!$wire.edit" wire:model="address.is_active"/>
            <x-toggle :text="__('Main Address')" x-bind:disabled="!$wire.edit || $wire.address.is_main_address" wire:model="address.is_main_address"/>
            <x-toggle :text="__('Delivery Address')" x-bind:disabled="!$wire.edit || $wire.address.is_delivery_address" wire:model="address.is_delivery_address"/>
            <x-toggle :text="__('Invoice Address')" x-bind:disabled="!$wire.edit || $wire.address.is_invoice_address" wire:model="address.is_invoice_address"/>
        </div>
        <h3 class="pt-12 text-lg font-medium leading-6 text-gray-900 dark:text-gray-50">
            {{ __('Contact options') }}
        </h3>
        <hr class="py-2" />
        @section('attributes.contact-options')
            <div class="flex flex-col gap-1.5" x-data="{edit: $wire.entangle('edit')}">
                <template x-for="(contactOption, index) in $wire.address.contact_options">
                    <div class="flex gap-1.5 items-center">
                        <div>
                            <x-native-select
                                x-bind:readonly="!edit"
                                x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                                x-model="contactOption.type"
                                :options="[
                                    ['label' => __('Email'), 'value' => 'email'],
                                    ['label' => __('Phone'), 'value' => 'phone'],
                                    ['label' => __('Website'), 'value' => 'website'],
                                ]"
                                option-label="label"
                                option-value="value"
                                :clearable="false"
                            />
                        </div>
                        <x-input x-model="contactOption.label" :placeholder="__('Label')" x-bind:disabled="!edit" x-bind:class="! edit && 'border-none bg-transparent shadow-none'"/>
                        <x-input x-model="contactOption.value" :placeholder="__('Value')" x-bind:disabled="!edit" x-bind:class="! edit && 'border-none bg-transparent shadow-none'"/>
                        <div x-transition x-show="edit">
                            <x-button icon="trash" negative x-on:click.prevent="$wire.address.contact_options.splice(index, 1)" x-bind:disabled="!edit"/>
                        </div>
                    </div>
                </template>
                <div x-transition x-show="edit">
                    <x-button icon="plus" :text="__('Add')" primary x-on:click.prevent="$wire.address.contact_options.push({type: 'email'})" x-bind:disabled="!edit"/>
                </div>
            </div>
        @show
        @section('attributes.map')
            <div x-data="addressMap($wire, 'address', true, '{{ auth()->user()?->getAvatarUrl() }}')" class="pt-6">
                <div id="map" x-show="showMap"></div>
            </div>
        @show
    @endif
@show
