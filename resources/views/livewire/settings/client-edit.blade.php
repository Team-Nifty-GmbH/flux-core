<div>
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="space-y-8 divide-y divide-gray-200">
            <div>
                <div class="mt-6 grid grid-cols-2 gap-y-6 gap-x-4">
                    <div class="">
                        <x-input label="{{ __('Name') }}"
                                 placeholder="{{ __('Name') }}"
                                 wire:model="client.name"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('Client Code') }}"
                                 placeholder="{{ __('Client Code') }}"
                                 wire:model="client.client_code"/>
                    </div>
                    <div class="">
                        <x-select
                            label="{{ __('Country') }}"
                            placeholder="{{ __('Country') }}"
                            wire:model="client.country_id"
                            :options="$countries"
                            option-label="name"
                            option-value="id"
                        />
                    </div>
                    <div class="">
                        <x-input label="{{ __('CEO') }}"
                                 placeholder="{{ __('CEO') }}"
                                 wire:model="client.ceo"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('Postcode') }}"
                                 placeholder="{{ __('Postcode') }}"
                                 wire:model="client.postcode"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('City') }}"
                                 placeholder="{{ __('City') }}"
                                 wire:model="client.city"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('Street') }}"
                                 placeholder="{{ __('Street') }}"
                                 wire:model="client.street"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('Phone') }}"
                                 placeholder="{{ __('Phone') }}"
                                 wire:model="client.phone"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('Fax') }}"
                                 placeholder="{{ __('Fax') }}"
                                 wire:model="client.fax"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('Email') }}"
                                 placeholder="{{ __('Email') }}"
                                 wire:model="client.email"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('Website') }}"
                                 placeholder="{{ __('Website') }}"
                                 wire:model="client.website"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('Bank Name') }}"
                                 placeholder="{{ __('Bank Name') }}"
                                 wire:model="client.bank_name"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('Bank Code') }}"
                                 placeholder="{{ __('Bank Code') }}"
                                 wire:model="client.bank_code"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('Bank Account') }}"
                                 placeholder="{{ __('Bank Account') }}"
                                 wire:model="client.bank_account"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('Bank Iban') }}"
                                 placeholder="{{ __('Bank Iban') }}"
                                 wire:model="client.bank_iban"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('Bank Swift') }}"
                                 placeholder="{{ __('Bank Swift') }}"
                                 wire:model="client.bank_swift"/>
                    </div>
                    <div class="">
                        <x-input label="{{ __('Bank BIC') }}"
                                 placeholder="{{ __('Bank BIC') }}"
                                 wire:model="client.bank_bic"/>
                    </div>
                    <div class="mt-8">
                        <x-checkbox :label="__('Active')" wire:model="client.is_active"/>
                    </div>
                </div>
                <div>
                    <x-table>
                        <x-slot:header>
                            <x-table.head-cell>
                                {{ __('Days') }}
                            </x-table.head-cell>
                            <x-table.head-cell>
                                {{ __('Start') }}
                            </x-table.head-cell>
                            <x-table.head-cell>
                                {{ __('End') }}
                            </x-table.head-cell>
                            <x-table.head-cell>
                            </x-table.head-cell>
                        </x-slot:header>
                        @foreach(($client['opening_hours'] ?? []) as $day => $hours)
                            <tr>
                                <td>
                                    <x-input wire:model="client.opening_hours.{{ $day }}.day"/>
                                </td>
                                <td>
                                    <x-input type="time" wire:model="client.opening_hours.{{ $day }}.start"/>
                                </td>
                                <td>
                                    <x-input type="time" wire:model="client.opening_hours.{{ $day }}.end"/>
                                </td>
                                <td>
                                    <x-button.circle icon="x" negative sm wire:click="removeDay({{ $day }})"/>
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                    <div class="flex w-full justify-center">
                        <div class="pt-4">
                            <x-button primary wire:click="addDay()">
                                {{ __('Add') }}
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
