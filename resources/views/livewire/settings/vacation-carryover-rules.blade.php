<div>
    <x-modal :id="$vacationCarryoverRuleForm->modalName()" size="2xl">
        <x-slot:title>
            {{ $vacationCarryoverRuleForm->id ? __('Edit Vacation Carryover Rule') : __('Create Vacation Carryover Rule') }}
        </x-slot:title>
    
    <div class="flex flex-col gap-4">
        @if(resolve_static(\FluxErp\Models\Client::class, 'query')->count() > 1)
            <x-select.styled
                wire:model="vacationCarryoverRuleForm.client_id"
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
        
        <x-input 
            wire:model="vacationCarryoverRuleForm.name" 
            :label="__('Name')" 
            required 
        />
        
        <x-number 
            wire:model="vacationCarryoverRuleForm.max_days" 
            min="0"
            max="365"
            :label="__('Maximum Carryover Days')" 
            :hint="__('Maximum vacation days that can be carried over to next year')"
        />
        
        <x-number 
            wire:model="vacationCarryoverRuleForm.expires_after_months" 
            min="0"
            max="24"
            :label="__('Expires After Months')" 
            :hint="__('Number of months after which carried over days expire')"
        />
        
        <x-toggle 
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
            color="primary"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('{{ $vacationCarryoverRuleForm->modalName() }}') })"
        />
    </x-slot>
    </x-modal>
</div>