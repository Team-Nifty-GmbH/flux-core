<x-modal id="edit-address-type-modal" :title="__('Address Type')">
    <div class="flex flex-col gap-1.5">
        <x-input wire:model="addressType.name" :label="__('Name')" required />
        <x-input
            wire:model="addressType.address_type_code"
            :label="__('Address Type Code')"
            required
        />
        @if(count($tenants) > 1)
            <div>
                <x-select.styled
                    :label="__('Tenants')"
                    autocomplete="off"
                    wire:model="addressType.tenants"
                    multiple
                    select="label:name|value:id"
                    :options="$tenants"
                />
            </div>
        @endif

        <div class="mt-2">
            <x-toggle
                :label="__('Is Unique')"
                wire:model="addressType.is_unique"
            />
        </div>
        <x-toggle :label="__('Is Locked')" wire:model="addressType.is_locked" />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('edit-address-type-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="
                $wire.save().then((success) => {
                    if (success) $tsui.close.modal('edit-address-type-modal');
                })
            "
        />
    </x-slot:footer>
</x-modal>
