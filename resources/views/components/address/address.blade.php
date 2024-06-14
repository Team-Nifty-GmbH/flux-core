<div class="table w-full table-auto gap-1.5" x-ref="address">
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.company') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Company') }}
        </label>
        <div class="col-span-2 w-full">
            <x-input x-bind:readonly="!$wire.edit"
                x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                wire:model="address.company"
            />
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.department') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Department') }}
        </label>
        <div class="col-span-2 w-full">
            <x-input x-bind:readonly="!$wire.edit"
                     x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                     wire:model="address.department"
            />
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.salutation') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Salutation') }}
        </label>
        <div class="col-span-2 w-full">
            <x-input x-bind:readonly="!$wire.edit"
                x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                wire:model="address.salutation"
            />
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.title') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Title') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!$wire.edit"
                x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                wire:model="address.title"
            />
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.firstname') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Firstname') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!$wire.edit"
                x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                wire:model="address.firstname"
            />
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.lastname') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Lastname') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!$wire.edit"
                x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                wire:model="address.lastname"
            />
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.street') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Street') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!$wire.edit"
                x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                wire:model="address.street"
            />
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.country_id') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Country') }}
        </label>
        <div class="col-span-2">
            <x-select x-bind:readonly="!$wire.edit"
                      x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                      wire:model="address.country_id"
                      searchable
                      :options="$countries"
                      option-label="name"
                      option-value="id"
            />
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="postal-code" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Zip / City') }}
        </label>
        <div class="mt-1 w-full items-center space-x-2 sm:col-span-2 sm:mt-0 sm:flex sm:space-x-2">
            <div class="flex-none">
                <x-input x-bind:readonly="!$wire.edit"
                    x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                    wire:model="address.zip"
                />
            </div>
            <div class="grow">
                <x-input x-bind:readonly="!$wire.edit"
                    x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                    wire:model="address.city"
                />
            </div>
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.date_of_birth') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Date Of Birth') }}
        </label>
        <div class="col-span-2">
            <x-datetime-picker wire:model="address.date_of_birth" :without-time="true" x-bind:disabled="!$wire.edit" />
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.email') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Email') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!$wire.edit"
                     class="pl-12"
                     x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                     wire:model="address.email">
                <x-slot:prepend>
                    <div class="absolute inset-y-0 left-0 flex items-center p-0.5">
                        <x-button
                            class="h-full rounded-l-md"
                            icon="mail"
                            primary
                            flat
                            squared
                            x-on:click.prevent="window.open('mailto:' + $wire.address.email)"
                        />
                    </div>
                </x-slot:prepend>
            </x-input>
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.phone') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Phone') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!$wire.edit"
                class="pl-12"
                x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                wire:model="address.phone"
            >
                <x-slot:prepend>
                    <div class="absolute inset-y-0 left-0 flex items-center p-0.5">
                        <x-button
                            class="h-full rounded-l-md"
                            icon="phone"
                            primary
                            flat
                            squared
                            x-on:click.prevent="window.open('tel:' + $wire.address.phone)"
                        />
                    </div>
                </x-slot:prepend>
            </x-input>
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.url') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('URL') }}
        </label>
        <div class="col-span-2">
            <x-input x-bind:readonly="!$wire.edit"
                     class="pl-12"
                     x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                     wire:model="address.url">
                <x-slot:prepend>
                    <div class="absolute inset-y-0 left-0 flex items-center p-0.5">
                        <x-button
                            class="h-full rounded-l-md"
                            icon="globe"
                            primary
                            flat
                            squared
                            x-on:click.prevent="window.open('//' + $wire.address.url)"
                        />
                    </div>
                </x-slot:prepend>
            </x-input>
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.language_id') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Language') }}
        </label>
        <div class="col-span-2">
            <x-select x-bind:disabled="!$wire.edit"
                      x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"
                      wire:model="address.language_id"
                      searchable
                      :options="$languages"
                      option-label="name"
                      option-value="id"
            />
        </div>
    </div>
    <div class="sm:table-row sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-2">
        <label for="{{ md5('address.tags') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
            {{ __('Tags') }}
        </label>
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
                                app(\FluxErp\Models\Address::class)->getMorphClass(),
                            ],
                        ],
                    ],
                ]"
            >
                <x-slot:beforeOptions>
                    <div class="px-1">
                        <x-button positive full :label="__('Add')" wire:click="addTag($promptValue())" wire:flux-confirm.prompt="{{ __('New Tag') }}||{{ __('Cancel') }}|{{ __('Save') }}" />
                    </div>
                </x-slot:beforeOptions>
            </x-select>
        </div>
    </div>
</div>
<h3 class="pt-12 text-lg font-medium leading-6 text-gray-900 dark:text-gray-50">
    {{ __('Attributes') }}
</h3>
<hr class="py-2" />
<div class="flex flex-col gap-1.5">
    <x-toggle :label="__('Active')" x-bind:disabled="!$wire.edit" wire:model="address.is_active"/>
    <x-toggle :label="__('Main Address')" x-bind:disabled="!$wire.edit || $wire.address.is_main_address" wire:model="address.is_main_address"/>
    <x-toggle :label="__('Delivery Address')" x-bind:disabled="!$wire.edit || $wire.address.is_delivery_address" wire:model="address.is_delivery_address"/>
    <x-toggle :label="__('Invoice Address')" x-bind:disabled="!$wire.edit || $wire.address.is_invoice_address" wire:model="address.is_invoice_address"/>
</div>
<h3 class="pt-12 text-lg font-medium leading-6 text-gray-900 dark:text-gray-50">
    {{ __('Contact options') }}
</h3>
<hr class="py-2" />
<div class="flex flex-col gap-1.5">
    <template x-for="(contactOption, index) in $wire.address.contact_options">
        <div class="flex gap-1.5 items-center">
            <x-input x-model="contactOption.type" :placeholder="__('Group')" x-bind:disabled="!$wire.edit" x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"/>
            <x-input x-model="contactOption.label" :placeholder="__('Label')" x-bind:disabled="!$wire.edit" x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"/>
            <x-input x-model="contactOption.value" :placeholder="__('Value')" x-bind:disabled="!$wire.edit" x-bind:class="! $wire.edit && 'border-none bg-transparent shadow-none'"/>
            <div x-transition x-show="$wire.edit">
                <x-button icon="trash" negative x-on:click.prevent="$wire.address.contact_options.splice(index, 1)" x-bind:disabled="!$wire.edit"/>
            </div>
        </div>
    </template>
    <div x-transition x-show="$wire.edit">
        <x-button icon="plus" :label="__('Add')" primary x-on:click.prevent="$wire.address.contact_options.push({})" x-bind:disabled="!$wire.edit"/>
    </div>
</div>
<div x-data="addressMap($wire)">
    <div id="map"></div>
</div>

