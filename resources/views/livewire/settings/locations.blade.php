<div>
    <x-modal
        :id="$locationForm->modalName()"
        size="2xl"
        :title="__('Location')"
    >
        <div class="flex flex-col gap-4">
            <x-input
                wire:model="locationForm.name"
                :label="__('Name')"
                required
            />

            <div class="grid grid-cols-3 gap-4">
                <x-input wire:model="locationForm.zip" :label="__('Zip')" />

                <x-input wire:model="locationForm.city" :label="__('City')" />

                <x-input
                    wire:model="locationForm.street"
                    :label="__('Street')"
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-select.styled
                    wire:model="locationForm.country_id"
                    :label="__('Country')"
                    :placeholder="__('Select')"
                    :clearable="true"
                    select="label:name|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\Country::class),
                        'method' => 'POST',
                        'params' => [
                            'searchFields' => ['name', 'iso_alpha2', 'iso_alpha3']
                        ]
                    ]"
                />

                <x-select.styled
                    wire:model="locationForm.country_region_id"
                    :label="__('State/Region')"
                    :placeholder="__('Select')"
                    :clearable="true"
                    select="label:name|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\CountryRegion::class),
                        'method' => 'POST',
                        'params' => [
                            'searchFields' => ['name'],
                            'where' => [
                                ['country_id', '=', $locationForm->country_id]
                            ]
                        ]
                    ]"
                />
            </div>

            <x-toggle
                wire:model="locationForm.is_active"
                :label="__('Is Active')"
            />
        </div>

        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$modalClose('{{ $locationForm->modalName() }}')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                wire:click="save().then((success) => { if(success) $modalClose('{{ $locationForm->modalName() }}') })"
            />
        </x-slot>
    </x-modal>
</div>
