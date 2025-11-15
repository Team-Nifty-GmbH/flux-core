<div class="space-y-8 divide-y divide-gray-200">
    <div class="space-y-8 divide-y divide-gray-200">
        <div>
            <div class="mt-6 grid grid-cols-2 gap-x-4 gap-y-2">
                <x-input
                    :label="__('Name')"
                    :placeholder="__('Name')"
                    wire:model="tenant.name"
                />
                <x-input
                    :label="__('Tenant Code')"
                    :placeholder="__('Tenant Code')"
                    wire:model="tenant.tenant_code"
                />
                <x-select.styled
                    :label="__('Country')"
                    :placeholder="__('Country')"
                    wire:model="tenant.country_id"
                    select="label:name|value:id"
                    :options="$countries"
                />
                <x-input
                    :label="__('CEO')"
                    :placeholder="__('CEO')"
                    wire:model="tenant.ceo"
                />
                <x-input
                    :label="__('Postcode')"
                    :placeholder="__('Postcode')"
                    wire:model="tenant.postcode"
                />
                <x-input
                    :label="__('City')"
                    :placeholder="__('City')"
                    wire:model="tenant.city"
                />
                <x-input
                    :label="__('Street')"
                    :placeholder="__('Street')"
                    wire:model="tenant.street"
                />
                <x-input
                    :label="__('Phone')"
                    :placeholder="__('Phone')"
                    wire:model="tenant.phone"
                />
                <x-input
                    :label="__('Fax')"
                    :placeholder="__('Fax')"
                    wire:model="tenant.fax"
                />
                <x-input
                    :label="__('Email')"
                    :placeholder="__('Email')"
                    wire:model="tenant.email"
                />
                <x-input
                    :label="__('Website')"
                    :placeholder="__('Website')"
                    wire:model="tenant.website"
                />
                <x-input
                    :label="__('Vat Id')"
                    :placeholder="__('Vat Id')"
                    wire:model="tenant.vat_id"
                />
                <x-input
                    :label="__('Tax Id')"
                    :placeholder="__('Tax Id')"
                    wire:model="tenant.tax_id"
                />
                <x-select.styled
                    :label="__('Bank Connections')"
                    multiple
                    wire:model="tenant.bank_connections"
                    select="label:name|value:id"
                    :options="$bankConnections"
                />
            </div>
            <div class="mt-2 flex flex-col gap-2">
                <x-toggle
                    :label="__('Active')"
                    wire:model="tenant.is_active"
                />
                <x-toggle
                    :label="__('Is Default')"
                    wire:model="tenant.is_default"
                />
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
                        <x-flux::table.head-cell></x-flux::table.head-cell>
                    </x-slot>
                    <template
                        x-for="(hours, index) in $wire.tenant.opening_hours"
                    >
                        <tr>
                            <td>
                                <x-input x-model="hours.day" />
                            </td>
                            <td>
                                <x-input type="time" x-model="hours.start" />
                            </td>
                            <td>
                                <x-input type="time" x-model="hours.end" />
                            </td>
                            <td>
                                <x-button.circle
                                    icon="trash"
                                    color="red"
                                    sm
                                    x-on:click="$wire.tenant.opening_hours.splice(index, 1)"
                                />
                            </td>
                        </tr>
                    </template>
                </x-flux::table>
                <div class="flex w-full justify-center">
                    <div class="pt-4">
                        <x-button
                            color="indigo"
                            x-on:click="$wire.tenant.opening_hours.push({})"
                        >
                            {{ __('Add') }}
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
