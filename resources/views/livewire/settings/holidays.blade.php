<div>
    <x-modal :id="$holidayForm->modalName()" size="2xl">
        <x-slot:title>
            <span x-text="$wire.holidayForm.id ? '{{ __('Edit Holiday') }}' : '{{ __('Create Holiday') }}'"></span>
        </x-slot:title>
        
        <div class="flex flex-col gap-4">
            <x-input wire:model="holidayForm.name" :label="__('Name')" required />
            
            <x-checkbox 
                wire:model.live="holidayForm.is_recurring" 
                :label="__('Recurring Holiday')" 
                :hint="__('Check for holidays that repeat every year')"
            />
            
            <div x-show="!$wire.holidayForm.is_recurring">
                <x-date 
                    wire:model="holidayForm.date" 
                    :label="__('Date')" 
                    required 
                />
            </div>
            
            <div class="grid grid-cols-2 gap-4" x-show="$wire.holidayForm.is_recurring">
                <x-input 
                    wire:model="holidayForm.month" 
                    type="number" 
                    min="1" 
                    max="12"
                    :label="__('Month')" 
                    required 
                />
                
                <x-input 
                    wire:model="holidayForm.day" 
                    type="number" 
                    min="1" 
                    max="31"
                    :label="__('Day')" 
                    required 
                />
            </div>
            
            <x-select.styled
                wire:model="holidayForm.location_id"
                :label="__('Location')"
                select="label:name|value:id"
                :request="['url' => route('search', \FluxErp\Models\Location::class), 'method' => 'POST']"
                unfiltered
                :hint="__('Leave empty for all locations')"
            />
            
            <div class="grid grid-cols-2 gap-4">
                <x-input 
                    wire:model="holidayForm.effective_from" 
                    type="number" 
                    min="2000"
                    max="2100"
                    :label="__('Effective From Year')" 
                />
                
                <x-input 
                    wire:model="holidayForm.effective_until" 
                    type="number" 
                    min="2000"
                    max="2100"
                    :label="__('Effective Until Year')" 
                    :hint="__('Leave empty for no end date')"
                />
            </div>
            
            <x-select.styled
                wire:model="holidayForm.day_part"
                :label="__('Day Part')"
                :options="[
                    ['value' => 'full', 'label' => __('Full Day')],
                    ['value' => 'first_half', 'label' => __('First Half')],
                    ['value' => 'second_half', 'label' => __('Second Half')]
                ]"
                select="label:label|value:value"
                required
            />
            
            <x-checkbox 
                wire:model="holidayForm.is_active" 
                :label="__('Is Active')" 
            />
        </div>
        
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('{{ $holidayForm->modalName() }}')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                x-on:click="$wire.save().then((success) => { if(success) $modalClose('{{ $holidayForm->modalName() }}')})"
            />
        </x-slot>
    </x-modal>
</div>