<div class="py-6"  x-data="{clients: @entangle('clients').defer, customerPortalUrl: '{{ route('settings.settings.customer-portal', ['client' => ':clientId']) }}' }">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold">{{ __('Clients') }}</h1>
                <div class="mt-2 text-sm text-gray-300">{{__('Here you can manage your clients...')}}</div>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <x-button primary :label="__('New Client')" wire:click="show()" />
            </div>
        </div>
        <div class="mt-8 flex flex-col">
            <div class="-my-2 -mx-4 sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                            <tr class="divide-x divide-gray-200">
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    {{ __('Name') }}
                                </th>
                                <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('Client Code') }}
                                </th>
                                <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('Country') }}
                                </th>
                                <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('Postcode') }}
                                </th>
                                <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('City') }}
                                </th>
                                <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('Street') }}
                                </th>
                                <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('Phone') }}
                                </th>
                                <th scope="col" class="py-2 pl-2 pr-2 text-left text-sm font-semibold text-gray-900">
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                            <template x-for="(client, index) in clients">
                                <tr class="divide-x divide-gray-200">
                                    <td x-text="client.name" class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6" />
                                    <td x-text="client.client_code" class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6" />
                                    <td x-text="client.country.name" class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6" />
                                    <td x-text="client.postcode" class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6" />
                                    <td x-text="client.street" class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6" />
                                    <td x-text="client.city" class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6" />
                                    <td x-text="client.phone" class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-900 sm:pl-6" />
                                    <td class="whitespace-nowrap py-2 pl-2 pr-2 text-center text-sm text-gray-500">
                                        <x-dropdown>
                                            <x-dropdown.item :label="__('Edit')"  x-on:click="$wire.show(index)" />
                                            <x-dropdown.item :label="__('Customer Portal')"  x-bind:href="customerPortalUrl.replace(':clientId', client.id)" />
                                            <x-dropdown.item :label="__('Logos')"  x-on:click="$wire.showLogos(client.id)" />
                                        </x-dropdown>
                                    </td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal.card max-width="6xl" z-index="z-30" wire:model.defer="showClientModal" :title="__('Edit Client')">
        <livewire:settings.client-edit/>
        <x-slot name="footer">
            <div x-data="{index: @entangle('index').defer}" class="w-full">
                <div
                    class="flex justify-between gap-x-4">
                    @if(user_can('api.clients.{id}.delete'))
                        <x-button x-bind:class="index > -1 || 'invisible'" flat negative label="{{ __('Delete') }}"
                                  @click="window.$wireui.confirmDialog({
                                                            title: '{{ __('Delete client') }}',
                                                            description: '{{ __('Do you really want to delete this client?') }}',
                                                            icon: 'error',
                                                            accept: {
                                                                label: '{{ __('Delete') }}',
                                                                method: 'delete',
                                                            },
                                                            reject: {
                                                                label: '{{ __('Cancel') }}',
                                                            }
                                                        }, '{{ $this->id }}')
                                                        " label="{{ __('Delete') }}"/>
                    @endif
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="$emitTo('settings.client-edit', 'save')"/>
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal.card>
    <x-modal.card wire:model.defer="showClientLogosModal" :title="__('Manage Logos')">
        <livewire:settings.client-logos/>
        <x-slot name="footer">
            <div class="w-full">
                    <div class="flex justify-end gap-x-4">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="$emitTo('settings.client-logos', 'save')"/>
                    </div>
            </div>
        </x-slot>
    </x-modal.card>
</div>
