<x-modal id="edit-address-type">
    <div class="flex flex-col gap-4">
        <x-input wire:model="addressType.name" :label="__('Name')" required/>
        <x-input wire:model="addressType.address_type_code" :label="__('Address Type Code')" required/>
        <div x-show="! $wire.addressType.id" x-cloak>
            <x-select.styled
                :label="__('Client')"
                select="label:name|value:id"
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
            <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-address-type')"/>
            <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-address-type')})"/>
        </div>
    </x-slot:footer>
</x-modal>
