<div>
    <x-modal :id="$vacationCarryoverRuleForm->modalName()" size="2xl">
        <x-slot:title>
            <span x-text="$wire.vacationCarryoverRuleForm.id ? '{{ __('Edit Vacation Carryover Rule') }}' : '{{ __('Create Vacation Carryover Rule') }}'"></span>
        </x-slot:title>
    
    <div class="flex flex-col gap-4">
        <x-input 
            wire:model="vacationCarryoverRuleForm.effective_year" 
            type="number" 
            min="2000"
            max="2100"
            :label="__('Effective Year')" 
            required 
        />
        
        <div class="grid grid-cols-2 gap-4">
            <x-input 
                wire:model="vacationCarryoverRuleForm.cutoff_month" 
                type="number" 
                min="1" 
                max="12"
                :label="__('Cutoff Month')" 
                :hint="__('Month when carryover is calculated')"
                required 
            />
            
            <x-input 
                wire:model="vacationCarryoverRuleForm.cutoff_day" 
                type="number" 
                min="1" 
                max="31"
                :label="__('Cutoff Day')" 
                required 
            />
        </div>
        
        <x-input 
            wire:model="vacationCarryoverRuleForm.max_carryover_days" 
            type="number" 
            min="0"
            max="365"
            :label="__('Max Carryover Days')" 
            :hint="__('Maximum days that can be carried over')"
        />
        
        <x-date 
            wire:model="vacationCarryoverRuleForm.expiry_date" 
            :label="__('Expiry Date')" 
            :hint="__('When carried over days expire')"
        />
        
        <x-checkbox 
            wire:model="vacationCarryoverRuleForm.is_active" 
            :label="__('Is Active')" 
        />
    </div>
    
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('{{ $vacationCarryoverRuleForm->modalName() }}')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="$wire.save().then((success) => { if(success) $modalClose('{{ $vacationCarryoverRuleForm->modalName() }}')})"
        />
    </x-slot>
    </x-modal>
</div>