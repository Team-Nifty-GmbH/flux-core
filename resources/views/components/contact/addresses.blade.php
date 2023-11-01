<div class="w-full lg:col-span-2 lg:col-start-1 lg:flex lg:space-x-6">
    <section class="flex flex-col gap-4 basis-1/4" wire:ignore x-data="{
        addresses: $wire.entangle('contact.addresses'),
        address: $wire.entangle('address'),
        selectAddress(address) {
            this.address = address;
            $wire.addressId = address.id;
            $dispatch('address-selected', { id: address.id });
        },
    }"
    x-on:address-deleted.window="addresses = addresses.filter(address => address.id !== $event.detail.id); selectAddress(addresses[0]);"
    x-on:address-created.window="addresses.push($event.detail); selectAddress($event.detail);"
    x-on:address-updated.window="addresses = addresses.map(address => address.id === $event.detail.id ? $event.detail : address); selectAddress($event.detail);"
    >
        <x-card>
            <div>
                <div
                    class="dark:border-secondary-700 flex items-center justify-between border-b border-gray-200 pb-5">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-50">{{ __('Addresses') }}</h3>
                    <div class="mt-3 sm:mt-0 sm:ml-4">
                        @can('action.address.create')
                            <x-button
                                x-on:click="$focus.within($refs.address).first(); edit = true;"
                                x-on:click="$dispatch('add-address')"
                                primary
                            >
                                {{ __('New address') }}
                            </x-button>
                        @endcan
                    </div>
                </div>
                <div class="space-y-2">
                    <template x-for="addressItem in addresses">
                        <div
                            x-on:click="selectAddress(addressItem)"
                            x-bind:class="address.id == addressItem.id && 'rounded-lg ring-2 ring-inset ring-primary-500 bg-blue-100 dark:bg-secondary-700'"
                            class="dark:hover:bg-secondary-800 cursor-pointer space-y-2 p-1.5 hover:bg-blue-50"
                        >
                            <div class="flex w-full justify-between">
                                <div class="text-sm dark:text-gray-50">
                                    <div class="font-semibold" x-text="addressItem.name"></div>
                                    <div x-text="addressItem.street"></div>
                                    <div x-text="((addressItem?.zip || '') + ' ' + (addressItem?.city || '')).trim()"></div>
                                </div>
                                <div class="flex-col space-y-2" x-show="addressItem.is_main_address">
                                    <x-badge green :label="__('Main address')" />
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </x-card>
        <x-card class="space-y-2.5">
            <x-select wire:model.live="contact.price_list_id" :clearable="false" :label="__('Price group')" :options="$this->priceLists" option-label="name" option-value="id"/>
            <x-select wire:model.live="contact.payment_type_id" :clearable="false" :label="__('Payment type')" :options="$this->paymentTypes" option-label="name" option-value="id"/>
            <x-select
                x-on:selected="$wire.changeCommissionAgent($event.detail.value)"
                :label="__('Commission Agent')"
                wire:model="contact.agent_id"
                option-value="id"
                option-label="label"
                :disabled="! user_can('action.contact.update')"
                :clearable="false"
                :template="[
                    'name'   => 'user-option',
                ]"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\User::class),
                    'method' => 'POST',
                    'params' => [
                        'with' => 'media',
                    ]
                ]"
            />
        </x-card>
    </section>
    <livewire:address.address :address="$this->address" :contact="$this->contact" wire:model="addressId" />
</div>
