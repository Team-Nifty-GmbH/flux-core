<div class="p-6">
    <div class="font-semibold text-2xl">
        <x-modal name="edit-address-type">
            <x-card>
                <div class="flex flex-col gap-4">
                    <x-input wire:model="addressType.name" :label="__('Name')" required/>
                    <x-input wire:model="addressType.address_type_code" :label="__('Address Type Code')" required/>
                    <div x-show="! $wire.addressType.id" x-cloak>
                        <x-select
                            :label="__('Client')"
                            option-value="id"
                            option-label="name"
                            autocomplete="off"
                            wire:model="addressType.client_id"
                            :options="$clients"
                        />
                    </div>
                    <x-toggle :label="__('Is Unique')" wire:model="addressType.is_unique"/>
                    <x-toggle :label="__('Is Locked')" wire:model="addressType.is_locked"/>
                </div>
                <x-slot:footer>
                    <div class="flex justify-end gap-1.5">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close()})"/>
                    </div>
                </x-slot:footer>
            </x-card>
        </x-modal>
    </div>
</div>
