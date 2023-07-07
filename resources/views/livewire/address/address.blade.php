<div
    x-data="{
        address: @entangle('address').defer,
        edit: @entangle('edit').defer,
        tab: @entangle('tab'),
        addresses: @entangle('addresses').defer,
    }"
    class="w-full lg:col-span-2 lg:col-start-1 lg:flex lg:space-x-6">

    <!-- Address list-->
    <section class="basis-1/4" wire:ignore>
        <x-card>
            <div>
                <div
                    class="dark:border-secondary-700 flex items-center justify-between border-b border-gray-200 pb-5">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-50">{{ __('Addresses') }}</h3>
                    <div class="mt-3 sm:mt-0 sm:ml-4">
                        @can('api.addresses.post')
                            <x-button
                                @click="$focus.within($refs.address).first(); edit = true;"
                                wire:click="addAddress"
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
                            x-on:click="$wire.getAddress(addressItem.id, tab !== 'comments'); edit = false"
                            x-bind:class="address.id == addressItem.id && 'rounded-lg ring-2 ring-inset ring-primary-500 bg-blue-100 dark:bg-secondary-700'"
                            class="dark:hover:bg-secondary-800 cursor-pointer space-y-2 p-1.5 hover:bg-blue-50"
                        >
                            <div class="flex w-full justify-between">
                                <div class="text-sm dark:text-gray-50">
                                    <p x-text="addressItem.name"></p>
                                    <div x-text="addressItem.street"></div>
                                    <div x-text="(addressItem.zip + ' ' + addressItem.city).trim()"></div>
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
    </section>
    <section class="basis-3/4 pt-6 lg:pt-0">
        <x-card>
            <div
                class="border-gray-200">
                <div class="space-y-6 sm:space-y-5">
                    <div class="flex justify-between">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-50" x-text="address.id ? '{{ __('Address') }}' + ' ' + address.id : '{{ __('New Address') }}'">
                            </h3>
                        </div>
                        <div wire:ignore>
                            @if($address['id'] ?? false)
                                @can('api.addresses.put')
                                    <x-button
                                        icon="pencil"
                                        x-cloak
                                        x-show="address.id"
                                        @click="edit = true"
                                        wire:click="edit"
                                        primary
                                    >
                                        <div class="hidden sm:block">
                                            {{ __('Edit') }}
                                        </div>
                                    </x-button>
                                @endcan
                                @can('api.addresses.post')
                                        <x-button
                                            x-cloak
                                            icon="document-duplicate"
                                            x-show="address.id"
                                            @click="edit = true"
                                            wire:click="duplicate"
                                        >
                                            <div class="hidden sm:block">
                                                {{ __('Duplicate') }}
                                            </div>
                                        </x-button>
                                @endcan
                                @can('api.addresses.{id}.delete')
                                    <x-button
                                        x-cloak
                                        icon="trash"
                                        x-show="! address.is_main_address && address.id"
                                        @click="
                                        window.$wireui.confirmDialog({
                                            title: '{{ __('Delete address') }}',
                                            description: '{{ __('Do you really want to delete this address?') }}',
                                            icon: 'error',
                                            accept: {
                                                label: '{{ __('Delete') }}',
                                                method: 'delete',
                                            },
                                            reject: {
                                                label: '{{ __('Cancel') }}',
                                            }
                                        }, '{{ $this->id }}')
                                        "
                                        negative>
                                        <div class="hidden sm:block">
                                            {{ __('Delete') }}
                                        </div>
                                    </x-button>
                                @endcan
                            @endif
                        </div>
                    </div>
                    <!-- Tabs -->
                    <x-tabs
                        wire:model="tab"
                        :tabs="[
                            'address' => __('General'),
                            'permissions' => __('Permissions'),
                            'comments' => __('Comments'),
                            'serial-numbers' => __('Serial numbers'),
                            'additional-columns' => __('Additional columns'),
                        ]"
                        wire:loading
                    >
                        <x-dynamic-component :component="'address.' . $tab" />
                    </x-tabs>
                </div>
                <div class="pb-6">
                    <div x-cloak x-show="edit" x-transition.duration.400ms
                         class="flex w-full space-x-6 pt-4">
                        <x-button x-on:click="edit = false" class="w-full"
                                  wire:click.prevent="cancel"
                                  spinner="cancel" negative primary>{{ __('Cancel') }}</x-button>
                        <x-button x-on:click="edit = false" class="w-full"
                                  wire:click.prevent="save"
                                  spinner="save" positive primary>{{ __('Save') }}</x-button>
                    </div>
                </div>
            </div>
        </x-card>
    </section>
</div>
