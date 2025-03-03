@use('\FluxErp\Enums\SalutationEnum')
@props([
    'onlyPostal' => false,
    'countries' => resolve_static(\FluxErp\Models\Country::class, 'query')->get(['id', 'name'])->toArray(),
    'languages' => resolve_static(\FluxErp\Models\Language::class, 'query')->get(['id', 'name'])->toArray(),
])
<div class="table w-full table-auto gap-1.5" x-ref="address">
    @section('contact')
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :label="__('Company')" for="{{ md5('address.company') }}" />
            <div class="col-span-2 w-full">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.company"/>
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :label="__('Department')" for="{{ md5('address.department') }}" />
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
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2" x-bind:class="!$wire.edit && 'pointer-events-none'">
            <x-label :label="__('Salutation')" for="{{ md5('address.salutation') }}" />
            <div class="col-span-2 w-full">
                <x-select.styled
                    :options="SalutationEnum::valuesLocalized()"
                    x-bind:readonly="!$wire.edit"
                    wire:model="address.salutation"
                />
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :label="__('Title')" for="{{ md5('address.title') }}" />
            <div class="col-span-2">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.title" />
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :label="__('Firstname')" for="{{ md5('address.firstname') }}" />
            <div class="col-span-2">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.firstname" />
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :label="__('Lastname')" for="{{ md5('address.lastname') }}" />
            <div class="col-span-2">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.lastname" />
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :label="__('Addition')" for="{{ md5('address.addition') }}" />
            <div class="col-span-2">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.addition" />
            </div>
        </div>
    @show
    @section('address')
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :label="__('Street')" for="{{ md5('address.street') }}" />
            <div class="col-span-2">
                <x-input x-bind:readonly="!$wire.edit" wire:model="address.street" />
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2" x-bind:class="!$wire.edit && 'pointer-events-none'">
            <x-label :label="__('Country')" for="{{ md5('address.country_id') }}" />
            <div class="col-span-2">
                <x-select.styled
                    x-bind:disabled="!$wire.edit"
                    wire:model="address.country_id"
                    searchable
                    :options="$countries"
                    select="label:name|value:id"
                />
            </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
            <x-label :label="__('Zip / City')" for="postal-code" />
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
                <x-label :label="__('Date Of Birth')" for="{{ md5('address.date_of_birth') }}" />
                <div class="col-span-2">
                    <x-date
                        wire:model="address.date_of_birth"
                        :without-time="true"
                        x-bind:disabled="!$wire.edit"
                    />
                </div>
            </div>
        @endif
        @section('contact-channels.email')
            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
                <x-label :label="__('Email')" for="{{ md5('address.email_primary') }}" />
                <div class="col-span-2">
                    <x-input x-bind:readonly="!$wire.edit" class="pl-12" wire:model="address.email_primary">
                        <x-slot:prefix>
                            <div class="absolute inset-y-0 left-0 flex items-center p-0.5">
                                <x-button
                                    class="h-full rounded-l-md"
                                    icon="envelope"
                                    color="indigo"
                                    flat
                                    squared
                                    x-on:click.prevent="window.open('mailto:' + $wire.address.email_primary)"
                                />
                            </div>
                        </x-slot:prefix>
                    </x-input>
                </div>
            </div>
        @show
        @section('contact-channels.phone')
            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
                <x-label :label="__('Phone')" for="{{ md5('address.phone') }}" />
                <div class="col-span-2">
                    <x-input x-bind:readonly="!$wire.edit" class="pl-12" wire:model="address.phone">
                        <x-slot:prefix>
                            <div class="absolute inset-y-0 left-0 flex items-center p-0.5">
                                <x-button
                                    class="h-full rounded-l-md"
                                    icon="phone"
                                    color="indigo"
                                    flat
                                    squared
                                    x-on:click.prevent="window.open('tel:' + $wire.address.phone)"
                                />
                            </div>
                        </x-slot:prefix>
                    </x-input>
                </div>
            </div>
        @show
        @section('contact-channels.phone_mobile')
            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
                <x-label :label="__('Phone Mobile')" for="{{ md5('address.phone_mobile') }}" />
                <div class="col-span-2">
                    <x-input x-bind:readonly="!$wire.edit" class="pl-12" wire:model="address.phone_mobile">
                        <x-slot:prefix>
                            <div class="absolute inset-y-0 left-0 flex items-center p-0.5">
                                <x-button
                                    class="h-full rounded-l-md"
                                    icon="phone"
                                    color="indigo"
                                    flat
                                    squared
                                    x-on:click.prevent="window.open('tel:' + $wire.address.phone_mobile)"
                                />
                            </div>
                        </x-slot:prefix>
                    </x-input>
                </div>
            </div>
        @show
        @if(! $onlyPostal)
            @section('contact-channels.url')
                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
                    <x-label :label="__('URL')" for="{{ md5('address.url') }}" />
                    <div class="col-span-2">
                        <x-input x-bind:readonly="!$wire.edit" class="pl-12" wire:model="address.url">
                            <x-slot:prefix>
                                <div class="absolute inset-y-0 left-0 flex items-center p-0.5">
                                    <x-button
                                        class="h-full rounded-l-md"
                                        icon="globe-alt"
                                        color="indigo"
                                        flat
                                        squared
                                        x-on:click.prevent="window.open('//' + $wire.address.url)"
                                    />
                                </div>
                            </x-slot:prefix>
                        </x-input>
                    </div>
                </div>
            @show
            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2" x-bind:class="!$wire.edit && 'pointer-events-none'">
                <x-label :label="__('Language')" for="{{ md5('address.language_id') }}" />
                <div class="col-span-2">
                    <x-select.styled
                        x-bind:disabled="!$wire.edit"
                        wire:model="address.language_id"
                        searchable
                        :options="$languages"
                        select="label:name|value:id"
                    />
                </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2" x-bind:class="!$wire.edit && 'pointer-events-none'">
                <x-label :label="__('Tags')" for="{{ md5('address.tags') }}" />
                <div class="col-span-2">
                    <x-select.styled
                        multiple
                        x-bind:disabled="! $wire.edit"
                        wire:model.number="address.tags"
                        :request="[
                            'url' => route('search', \FluxErp\Models\Tag::class),
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
                        <x-slot:after>
                            @canAction(\FluxErp\Actions\Tag\CreateTag::class)
                                <div class="px-1">
                                    <x-button class="w-full" color="emerald" :text="__('Add')" wire:click="addTag($promptValue())" wire:flux-confirm.prompt="{{ __('New Tag') }}||{{ __('Cancel') }}|{{ __('Save') }}" />
                                </div>
                            @endCanAction
                        </x-slot:after>
                    </x-select.styled>
                </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2" x-bind:class="!$wire.edit && 'pointer-events-none'">
                <x-label :label="__('Advertising State')" for="{{ md5('address.advertising_state') }}" />
                <div class="col-span-2">
                    <x-flux::state
                        x-bind:class="!$wire.edit && 'pointer-events-none'"
                        class="w-full"
                        align="bottom-start"
                        wire:model="address.advertising_state"
                        formatters="formatter.advertising_state"
                        available="availableStates"
                    />
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
            @section('attributes.toggles')
                <x-toggle
                    :label="__('Active')"
                    x-bind:disabled="!$wire.edit"
                    wire:model="address.is_active"
                />
                <x-toggle :label="__('Main Address')" x-bind:disabled="!$wire.edit || $wire.address.is_main_address" wire:model="address.is_main_address"/>
                <x-toggle :label="__('Delivery Address')" x-bind:disabled="!$wire.edit || $wire.address.is_delivery_address" wire:model="address.is_delivery_address"/>
                <x-toggle :label="__('Invoice Address')" x-bind:disabled="!$wire.edit || $wire.address.is_invoice_address" wire:model="address.is_invoice_address"/>
            @show
        </div>
        <h3 class="pt-12 text-lg font-medium leading-6 text-gray-900 dark:text-gray-50">
            {{ __('Contact options') }}
        </h3>
        <hr class="py-2" />
        @section('attributes.contact-options')
            <div
                class="flex flex-col gap-1.5"
                x-data="{
                    edit: $wire.entangle('edit'),
                    hrefFromContactOption(type, value) {
                        switch (type) {
                            case 'phone':
                                return 'tel:' + value;
                            case 'email':
                                return 'mailto:' + value;
                            case 'website':
                                return value;
                            default:
                                return '#';
                        }
                    }
                }"
            >
                <template x-for="(contactOption, index) in $wire.address.contact_options">
                    <div class="flex gap-1.5 items-center">
                        <div>
                            <x-select.native
                                x-bind:readonly="!edit"
                                x-bind:class="! edit && 'border-none bg-transparent shadow-none'"
                                x-model="contactOption.type"
                                :options="[
                                    ['label' => __('Email'), 'value' => 'email'],
                                    ['label' => __('Phone'), 'value' => 'phone'],
                                    ['label' => __('Website'), 'value' => 'website'],
                                ]"
                                select="label:value|value:label"
                                required
                            />
                        </div>
                        <x-input x-model="contactOption.label" :placeholder="__('Label')" x-bind:disabled="!edit" x-bind:class="! edit && 'border-none bg-transparent shadow-none'"/>
                        <x-input x-cloak x-show="edit" x-model="contactOption.value" :placeholder="__('Value')" x-bind:disabled="!edit" x-bind:class="! edit && 'border-none bg-transparent shadow-none'"/>
                        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px">
                            <a x-bind:href="hrefFromContactOption(contactOption.type, contactOption.value)" x-text="contactOption.value" x-cloak x-show="!edit"></a>
                        </div>
                        <div x-transition x-cloak x-show="edit">
                            <x-button icon="trash" color="red" x-on:click.prevent="$wire.address.contact_options.splice(index, 1)" x-bind:disabled="!edit"/>
                        </div>
                    </div>
                </template>
                <div x-transition x-cloak x-show="edit">
                    <x-button icon="plus" :text="__('Add')" color="indigo" x-on:click.prevent="$wire.address.contact_options.push({type: 'email'})" x-bind:disabled="!edit"/>
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
