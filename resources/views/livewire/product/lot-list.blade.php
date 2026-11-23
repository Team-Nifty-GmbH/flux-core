<x-modal id="edit-lot-modal" :title="__('Lot')">
    <div class="flex flex-col gap-1.5">
        <x-input
            wire:model="lot.lot_number"
            :label="__('Lot Number')"
            required
        />
        <x-input
            wire:model="lot.supplier_lot_number"
            :label="__('Supplier Lot Number')"
        />
        <x-select.styled
            wire:model="lot.country_id"
            :label="__('Country of Origin')"
            select="label:name|value:id"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\Country::class),
                'method' => 'POST',
                'params' => [
                    'searchFields' => ['name', 'iso_alpha2', 'iso_alpha3'],
                ],
            ]"
        />
        <x-date wire:model="lot.produced_at" :label="__('Produced At')" />
        <x-date wire:model="lot.expires_at" :label="__('Expires At')" />
        <x-input
            type="datetime-local"
            wire:model="lot.blocked_at"
            :label="__('Blocked At')"
        />
        <x-textarea wire:model="lot.description" :label="__('Description')" />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('edit-lot-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="
                $wire.save().then((success) => {
                    if (success) $tsui.close.modal('edit-lot-modal');
                })
            "
        />
    </x-slot:footer>
</x-modal>
