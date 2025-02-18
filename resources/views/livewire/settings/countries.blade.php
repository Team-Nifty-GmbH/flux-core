<x-modal name="edit-country">
    <x-card>
        <div class="flex flex-col gap-4">
            <x-input wire:model="country.name" :label="__('Country Name')"/>
            <x-select
                wire:model="country.language_id"
                :label="__('Language')"
                :options="$languages"
                option-key-value
            />
            <x-select
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
                <x-button flat :label="__('Cancel')" x-on:click="close"/>
                <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close()})"/>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>
