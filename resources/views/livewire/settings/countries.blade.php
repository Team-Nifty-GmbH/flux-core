<x-modal id="edit-country-modal">
    <div class="flex flex-col gap-1.5">
        <x-input wire:model="country.name" :label="__('Country Name')" />
        <x-select.styled
            wire:model="country.language_id"
            :label="__('Language')"
            select="label:name|value:id"
            :options="$languages"
        />
        <x-select.styled
            wire:model="country.currency_id"
            :label="__('Currency')"
            select="label:name|value:id"
            :options="$currencies"
        />
        <x-input wire:model="country.iso_alpha2" :label="__('ISO alpha2')" />
        <x-input wire:model="country.iso_alpha3" :label="__('ISO alpha3')" />
        <x-input wire:model="country.iso_numeric" :label="__('ISO numeric')" />
        <x-toggle wire:model="country.is_active" lg :label="__('Active')" />
        <x-toggle wire:model="country.is_default" lg :label="__('Default')" />
        <x-toggle
            wire:model="country.is_eu_country"
            lg
            :label="__('EU Country')"
        />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-country-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-country-modal')})"
        />
    </x-slot>
</x-modal>
