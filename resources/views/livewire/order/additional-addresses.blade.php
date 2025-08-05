<div class="flex flex-col gap-4">
    <div wire:ignore>
        @teleport('body')
            <x-modal id="edit-address-assignment">
                <div class="flex flex-col gap-4">
                    <x-select.styled
                        :label="__('Address')"
                        wire:model="address_id"
                        select="label:label|value:id"
                        unfiltered
                        :request="[
                            'url' => route('search', \FluxErp\Models\Address::class),
                            'method' => 'POST',
                            'params' => [
                                'fields' => [
                                  'contact_id',
                                  'name',
                                ],
                                'with' => 'contact.media',
                                'where' => [
                                    [
                                        'client_id',
                                        '=',
                                        $clientId
                                    ],
                                ],
                            ],
                        ]"
                    />
                    <x-select.styled
                        :label="__('Type')"
                        wire:model="address_type_id"
                        select="label:name|value:id"
                        :options="resolve_static(\FluxErp\Models\AddressType::class, 'query')->where('client_id', $clientId)->get(['id', 'name'])->toArray()"
                    />
                </div>
                <x-slot:footer>
                    <x-button
                        color="secondary"
                        light
                        flat
                        :text="__('Cancel')"
                        x-on:click="$modalClose('edit-address-assignment')"
                    />
                    <x-button
                        color="indigo"
                        loading="save"
                        x-on:click="$wire.save().then((success) => {if(success) $modalClose('edit-address-assignment');})"
                        :text="__('Save')"
                    />
                </x-slot>
            </x-modal>
        @endteleport
    </div>
    @foreach ($form->addresses as $address)
        <x-card :header="$address['address_type']">
            <div class="text-sm">
                {!! implode('<br>', $address['address']) !!}
            </div>
            <x-slot:header>
                <div class="flex gap-1.5">
                    <x-button.circle
                        color="secondary"
                        light
                        wire:navigate
                        outline
                        icon="eye"
                        :href="route('address.id', data_get($address, 'address_id', ''))"
                    />
                    <x-button.circle
                        icon="trash"
                        wire:click="delete({{ data_get($address, 'address_id') }})"
                        color="red"
                        wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Address assignment')]) }}"
                    />
                </div>
            </x-slot>
        </x-card>
    @endforeach

    <x-button
        color="indigo"
        :text="__('Add additional address')"
        x-on:click="$modalOpen('edit-address-assignment')"
    />
</div>
