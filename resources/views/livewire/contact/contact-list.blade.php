@use('\FluxErp\Enums\SalutationEnum')
<div>
    @section('modals')
    @canAction(\FluxErp\Actions\Contact\CreateContact::class)
        <x-modal
            id="create-contact-modal"
            x-on:close="$wire.resetForm()"
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
                    x-on:click="$modalClose('create-contact-modal')"
                />
                <x-button
                    color="indigo"
                    :text="__('Save') "
                    wire:click="save().then((success) => {if(success) $modalClose('create-contact-modal');})"
                />
            </x-slot>
        </x-modal>
    @endcanAction

    @show
</div>
