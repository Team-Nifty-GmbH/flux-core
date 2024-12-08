<div class="flex flex-col gap-4">
    <div wire:ignore>
        @teleport('body')
            <x-modal name="edit-address-assignment">
                <x-card>
                    <div class="flex flex-col gap-4">
                        <x-select
                            :label="__('Address')"
                            option-value="id"
                            option-label="label"
                            template="user-option"
                            wire:model="address_id"
                            :async-data="[
                                'api' => route('search', \FluxErp\Models\Address::class),
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
                                ]
                            ]"
                        />
                        <x-select
                            :label="__('Type')"
                            option-key-value
                            wire:model="address_type_id"
                            :options="resolve_static(\FluxErp\Models\AddressType::class, 'query')->where('client_id', $clientId)->pluck('name', 'id')"
                        />
                    </div>
                    <x-slot:footer>
                        <div class="flex justify-end gap-x-4">
                            <x-button flat :label="__('Cancel')" x-on:click="close()" />
                            <x-button primary spinner x-on:click="$wire.save().then((success) => {if(success) close();})" :label="__('Save')" />
                        </div>
                    </x-slot:footer>
                </x-card>
            </x-modal>
        @endteleport
    </div>
    @foreach($form->addresses as $address)
        <x-card :title="$address['address_type']">
            <div class="text-sm">
                {!! implode('<br>', $address['address']) !!}
            </div>
            <x-slot:action>
                <div class="flex gap-1.5">
                    <x-mini-button
                        icon="trash"
                        wire:click="delete({{ $address['address_id'] }})"
                        negative
                        wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Address assignment')]) }}"
                    />
                </div>
            </x-slot:action>
        </x-card>
    @endforeach
    <x-button primary :label="__('Add additional address')" x-on:click="$openModal('edit-address-assignment')" />
</div>
