<div class="space-y-8 divide-y divide-gray-200">
    <div class="space-y-8 divide-y divide-gray-200">
        <div>
            <div class="mt-6 grid grid-cols-2 gap-y-6 gap-x-4">
                <x-input :label="__('Name')" :placeholder="__('Name')" wire:model="client.name"/>
                <x-input :label="__('Client Code')" :placeholder="__('Client Code')" wire:model="client.client_code"/>
                <x-select
                    :label="__('Country')"
                    :placeholder="__('Country')"
                    wire:model="client.country_id"
                    :options="$countries"
                    option-label="name"
                    option-value="id"
                />
                <x-input :label="__('CEO')" :placeholder="__('CEO')" wire:model="client.ceo"/>
                <x-input :label="__('Postcode')" :placeholder="__('Postcode')" wire:model="client.postcode"/>
                <x-input :label="__('City')" :placeholder="__('City')" wire:model="client.city"/>
                <x-input :label="__('Street')" :placeholder="__('Street')" wire:model="client.street"/>
                <x-input :label="__('Phone')" :placeholder="__('Phone')" wire:model="client.phone"/>
                <x-input :label="__('Fax')" :placeholder="__('Fax')" wire:model="client.fax"/>
                <x-input :label="__('Email')" :placeholder="__('Email')" wire:model="client.email"/>
                <x-input :label="__('Website')" :placeholder="__('Website')" wire:model="client.website"/>
                <x-input :label="__('Vat Id')" :placeholder="__('Vat Id')" wire:model="client.vat_id"/>
                <x-select
                    :label="__('Bank Connections')"
                    multiselect
                    wire:model="client.bank_connections"
                    :options="$bankConnections"
                    option-label="name"
                    option-value="id"
                />
                <x-toggle :label="__('Active')" wire:model="client.is_active"/>
                <x-toggle :label="__('Is Default')" wire:model="client.is_default"/>
            </div>
            <div>
                <x-flux::table>
                    <x-slot:header>
                        <x-flux::table.head-cell>
                            {{ __('Days') }}
                        </x-flux::table.head-cell>
                        <x-flux::table.head-cell>
                            {{ __('Start') }}
                        </x-flux::table.head-cell>
                        <x-flux::table.head-cell>
                            {{ __('End') }}
                        </x-flux::table.head-cell>
                        <x-flux::table.head-cell>
                        </x-flux::table.head-cell>
                    </x-slot:header>
                    <template x-for="(hours, index) in $wire.client.opening_hours">
                        <tr>
                            <td>
                                <x-input x-model="hours.day"/>
                            </td>
                            <td>
                                <x-input type="time" x-model="hours.start"/>
                            </td>
                            <td>
                                <x-input type="time" x-model="hours.end"/>
                            </td>
                            <td>
                                <x-mini-button rounded icon="trash" negative sm x-on:click="$wire.client.opening_hours.splice(index, 1)"/>
                            </td>
                        </tr>
                    </template>
                </x-flux::table>
                <div class="flex w-full justify-center">
                    <div class="pt-4">
                        <x-button primary x-on:click="$wire.client.opening_hours.push({})">
                            {{ __('Add') }}
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
