<div>
    <x-modal :id="$locationForm->modalName()" size="2xl">
        <x-slot:title>
            {{ $locationForm->id ? __('Edit Location') : __('Create Location') }}
        </x-slot:title>
        
        <div class="flex flex-col gap-4">
            @if(resolve_static(\FluxErp\Models\Client::class, 'query')->count() > 1)
                <x-select.styled
                    wire:model="locationForm.client_id"
                    :label="__('Client')"
                    select="label:name|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\Client::class),
                        'method' => 'POST',
                        'params' => [
                            'searchFields' => ['name', 'client_code']
                        ]
                    ]"
                />
            @endif
            
            <x-input wire:model="locationForm.name" :label="__('Name')" required />
            
            <div class="grid grid-cols-2 gap-4">
                <x-input 
                    wire:model="locationForm.street" 
                    :label="__('Street')" 
                />
                
                <x-input 
                    wire:model="locationForm.house_number" 
                    :label="__('House Number')" 
                />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <x-input 
                    wire:model="locationForm.zip" 
                    :label="__('ZIP Code')" 
                />
                
                <x-input 
                    wire:model="locationForm.city" 
                    :label="__('City')" 
                />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <x-select.styled
                    wire:model="locationForm.country_id"
                    :label="__('Country')"
                    :placeholder="__('Select Country')"
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
                    :placeholder="__('Select State/Region')"
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
            
            <div class="grid grid-cols-2 gap-4">
                <x-input 
                    wire:model="locationForm.latitude" 
                    type="number" 
                    step="0.00000001"
                    min="-90"
                    max="90"
                    :label="__('Latitude')" 
                />
                
                <x-input 
                    wire:model="locationForm.longitude" 
                    type="number" 
                    step="0.00000001"
                    min="-180"
                    max="180"
                    :label="__('Longitude')" 
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
                wire:click="save"
            />
        </x-slot:footer>
    </x-modal>
</div>