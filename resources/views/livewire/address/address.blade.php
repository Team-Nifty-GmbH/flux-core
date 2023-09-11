<section class="basis-3/4 pt-6 lg:pt-0"
     x-data="{
        address: $wire.entangle('address'),
        edit: $wire.entangle('edit'),
        tab: $wire.entangle('tab', true),
        deleteAddressDialog() {
            window.$wireui.confirmDialog({
                title: '{{ __('Delete address') }}',
                description: '{{ __('Do you really want to delete this address?') }}',
                icon: 'error',
                accept: {
                    label: '{{ __('Delete') }}',
                    execute: () => {
                        $wire.delete().then((address) => {
                            edit = false;
                            $dispatch('address-deleted', address)
                        });
                    },
                },
                reject: {
                    label: '{{ __('Cancel') }}',
                }
            }, '{{ $this->getId() }}')
        }
    }"
    x-on:address-selected.window="edit = false; $wire.getAddress($event.detail.id, tab !== 'comments')"
    x-on:add-address.window="$wire.addAddress()"
>
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
                            @can('action.address.update')
                                <x-button
                                    icon="pencil"
                                    x-cloak
                                    x-show="address.id"
                                    wire:click="edit"
                                    primary
                                >
                                    <div class="hidden sm:block">
                                        {{ __('Edit') }}
                                    </div>
                                </x-button>
                            @endcan
                            @can('action.address.create')
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
                            @can('action.address.delete')
                                <x-button
                                    x-cloak
                                    icon="trash"
                                    x-show="! address.is_main_address && address.id"
                                    x-on:click="deleteAddressDialog()"
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
                    wire:model.live="tab"
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
                    <x-button class="w-full"
                              x-on:click.prevent="
                                let newAddress = ! address.hasOwnProperty('id');
                                $wire.save().then(
                                    (address) => {
                                        if(address === null) {
                                            return;
                                        }

                                        edit = false;
                                        if (newAddress) {
                                            $dispatch('address-created', address);
                                        } else {
                                            $dispatch('address-updated', address);
                                        }
                                    }
                                )"
                              spinner="save" positive primary>{{ __('Save') }}</x-button>
                </div>
            </div>
        </div>
    </x-card>
</section>
