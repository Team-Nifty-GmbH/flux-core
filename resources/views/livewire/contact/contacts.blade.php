@use('\FluxErp\Enums\SalutationEnum')
<div>
    {{ $this->renderCreateDocumentsModal() }}
    @section('modals')
    @canAction(\FluxErp\Actions\Lead\CreateLead::class)
        <x-modal
            :id="$leadForm->modalName()"
            x-on:open="$focusOn('lead-name')"
            persistent
        >
            <div class="flex flex-col gap-4">
                <x-input
                    id="lead-name"
                    :label="__('Name')"
                    wire:model="leadForm.name"
                />
                <x-textarea
                    :label="__('Description')"
                    wire:model="leadForm.description"
                />
                <x-rating
                    wire:model.number="leadForm.score"
                    :text="__('Score')"
                    :quantity="5"
                    position="right"
                />
                @if (is_null(resolve_static(\FluxErp\Models\LeadState::class, 'default')?->probability_percentage))
                    <x-range
                        wire:model.number="leadForm.probability_percentage"
                        :hint="__('Probability to win this lead…')"
                    >
                        <x-slot:label>
                            <span
                                x-cloak
                                x-show="$wire.leadForm.probability_percentage !== null"
                                x-text="$wire.leadForm.probability_percentage + '%'"
                            ></span>
                        </x-slot>
                    </x-range>
                @endif
            </div>
            <x-slot:footer>
                <x-button
                    color="secondary"
                    light
                    :text="__('Cancel')"
                    x-on:click="$modalClose('{{ $leadForm->modalName() }}')"
                />
                <x-button
                    color="indigo"
                    :text="__('Save')"
                    wire:click="createLeads().then((success) => {if(success) $modalClose('{{ $leadForm->modalName() }}');})"
                />
            </x-slot>
        </x-modal>
    @endcanAction

    @canAction(\FluxErp\Actions\Contact\CreateContact::class)
        <x-modal
            id="new-contact-modal"
            x-on:open="$focusOn('contact-company')"
            persistent
        >
            @if (resolve_static(\FluxErp\Models\Client::class, 'query')->count() > 1)
                <x-select.styled
                    wire:model="contact.client_id"
                    label="{{ __('Client') }}"
                    select="label:name|value:id"
                    :options="resolve_static(\FluxErp\Models\Client::class, 'query')->get(['id', 'name'])"
                />
            @endif

            <div class="flex flex-col gap-1.5 pt-1.5">
                @section('contact')
                <div
                    class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-gray-200"
                >
                    <label
                        for="{{ md5('contact.company') }}"
                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
                    >
                        {{ __('Company') }}
                    </label>
                    <div class="col-span-2">
                        <x-input
                            id="contact-company"
                            x-ref="company"
                            wire:model="contact.main_address.company"
                        />
                    </div>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
                    <label
                        for="{{ md5('address.salutation') }}"
                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
                    >
                        {{ __('Salutation') }}
                    </label>
                    <div class="col-span-2 w-full">
                        <x-select.styled
                            x-bind:readonly="!$wire.edit"
                            wire:model="contact.main_address.salutation"
                            :options="SalutationEnum::valuesLocalized()"
                        />
                    </div>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
                    <label
                        for="{{ md5('address.title') }}"
                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
                    >
                        {{ __('Title') }}
                    </label>
                    <div class="col-span-2">
                        <x-input wire:model="contact.main_address.title" />
                    </div>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
                    <label
                        for="{{ md5('address.firstname') }}"
                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
                    >
                        {{ __('Firstname') }}
                    </label>
                    <div class="col-span-2">
                        <x-input wire:model="contact.main_address.firstname" />
                    </div>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
                    <label
                        for="{{ md5('address.lastname') }}"
                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
                    >
                        {{ __('Lastname') }}
                    </label>
                    <div class="col-span-2">
                        <x-input wire:model="contact.main_address.lastname" />
                    </div>
                </div>
                @show
                @section('address')
                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
                    <label
                        for="{{ md5('address.street') }}"
                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
                    >
                        {{ __('Street') }}
                    </label>
                    <div class="col-span-2">
                        <x-input wire:model="contact.main_address.street" />
                    </div>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
                    <label
                        for="postal-code"
                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
                    >
                        {{ __('Zip / City') }}
                    </label>
                    <div
                        class="mt-1 w-full items-center space-x-2 sm:col-span-2 sm:mt-0 sm:flex sm:space-x-2"
                    >
                        <div class="flex-none">
                            <x-input wire:model="contact.main_address.zip" />
                        </div>
                        <div class="grow">
                            <x-input wire:model="contact.main_address.city" />
                        </div>
                    </div>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
                    <label
                        for="{{ md5('address.countryId') }}"
                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
                    >
                        {{ __('Country') }}
                    </label>
                    <div class="col-span-2">
                        <x-select.styled
                            wire:model="contact.main_address.country_id"
                            searchable
                            select="label:name|value:id"
                            :options="resolve_static(\FluxErp\Models\Country::class, 'query')->get(['id', 'name'])"
                        />
                    </div>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
                    <label
                        for="{{ md5('address.language_id') }}"
                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
                    >
                        {{ __('Language') }}
                    </label>
                    <div class="col-span-2">
                        <x-select.styled
                            wire:model="contact.main_address.language_id"
                            searchable
                            select="label:name|value:id"
                            :options="resolve_static(\FluxErp\Models\Language::class, 'query')->get(['id', 'name'])"
                        />
                    </div>
                </div>
                @show
                @section('contact-channels')
                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
                    <label
                        for="{{ md5('contact.main_address.email_primary') }}"
                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
                    >
                        {{ __('Email') }}
                    </label>
                    <div class="col-span-2">
                        <x-input
                            x-bind:readonly="!$wire.edit"
                            wire:model="contact.main_address.email_primary"
                        />
                    </div>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
                    <label
                        for="{{ md5('contact.main_address.phone') }}"
                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
                    >
                        {{ __('Phone') }}
                    </label>
                    <div class="col-span-2">
                        <x-input
                            x-bind:readonly="!$wire.edit"
                            wire:model="contact.main_address.phone"
                        />
                    </div>
                </div>
                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
                    <label
                        for="{{ md5('contact.main_address.phone_mobile') }}"
                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
                    >
                        {{ __('Phone Mobile') }}
                    </label>
                    <div class="col-span-2">
                        <x-input
                            x-bind:readonly="!$wire.edit"
                            wire:model="contact.main_address.phone_mobile"
                        />
                    </div>
                </div>
                @show
                @section('contact-origin')
                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
                    <label
                        for="{{ md5('contact.contact_origin_id') }}"
                        class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
                    >
                        {{ __('Contact Origin') }}
                    </label>
                    <div class="col-span-2">
                        <x-select.styled
                            wire:model="contact.contact_origin_id"
                            searchable
                            select="label:name|value:id"
                            :options="resolve_static(\FluxErp\Models\ContactOrigin::class, 'query')->where('is_active', true)->get(['id', 'name'])"
                        />
                    </div>
                </div>
                @show
            </div>
            <x-slot:footer>
                <x-button
                    color="secondary"
                    light
                    flat
                    :text="__('Cancel') "
                    x-on:click="$modalClose('new-contact-modal')"
                />
                <x-button
                    color="indigo"
                    :text="__('Save') "
                    wire:click="save"
                />
            </x-slot>
        </x-modal>
    @endcanAction

    @show

    @section('map')
    <div>
        <div
            x-on:load-map.window="$nextTick(() => onChange())"
            class="z-0 py-4"
            x-data="addressMap($wire, 'loadMap', false, '{{ auth()->user() ?->getAvatarUrl() }}')"
            x-cloak
            x-show="$wire.showMap"
            x-collapse
        >
            <x-card class="w-full">
                <x-slot:header>
                    <x-button.circle
                        color="secondary"
                        light
                        wire:click="$set('showMap', false, true)"
                        icon="x-mark"
                    />
                </x-slot>
                <div x-intersect.once="onChange()">
                    <div id="map" class="h-96 min-w-96"></div>
                </div>
            </x-card>
        </div>
    </div>
    @show
</div>
