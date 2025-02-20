<x-modal id="edit-country">
    <div class="flex flex-col gap-4">
        <x-input wire:model="country.name" :label="__('Country Name')"/>
        <x-select.styled
            wire:model="country.language_id"
            :label="__('Language')"
            :options="$languages"
            option-key-value
        />
        <x-select.styled
            wire:model="country.currency_id"
            :label="__('Currency')"
            :options="$currencies"
            option-key-value
        />
        <x-input wire:model="country.iso_alpha2" :label="__('ISO alpha2')"/>
        <x-input wire:model="country.iso_alpha3" :label="__('ISO alpha3')"/>
        <x-input wire:model="country.iso_numeric" :label="__('ISO numeric')"/>
        <x-toggle wire:model="country.is_active" lg :label="__('Active')"/>
        <x-toggle wire:model="country.is_default" lg :label="__('Default')"/>
        <x-toggle wire:model="country.is_eu_country" lg :label="__('EU Country')"/>
    </div>
    <x-slot:footer>
        <div class="flex justify-end gap-1.5">
            <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-country')"/>
            <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-country')})"/>
        </div>
    </x-slot:footer>
</x-modal>
