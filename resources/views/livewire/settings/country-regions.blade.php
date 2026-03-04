<x-modal id="country-region-form-modal" :title="__('Country Region')">
    <div class="flex flex-col gap-2">
        <x-select.styled
            :label="__('Country')"
            wire:model="countryRegionForm.country_id"
            required
            select="label:name|value:id"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\Country::class),
                'method' => 'POST',
            ]"
        />
        <x-input wire:model="countryRegionForm.name" :label="__('Name')" />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('country-region-form-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('country-region-form-modal')})"
        />
    </x-slot>
</x-modal>
