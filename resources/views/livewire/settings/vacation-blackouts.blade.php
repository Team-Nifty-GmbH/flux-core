<div>
    <x-modal :id="$vacationBlackoutForm->modalName()" size="2xl">
        <x-slot:title>
            <span x-text="$wire.vacationBlackoutForm.id ? '{{ __('Edit Vacation Blackout') }}' : '{{ __('Create Vacation Blackout') }}'"></span>
        </x-slot:title>
        
        <div class="flex flex-col gap-4">
            <x-input 
                wire:model="vacationBlackoutForm.name" 
                :label="__('Name')" 
                required 
            />
            
            <div class="grid grid-cols-2 gap-4">
                <x-date 
                    wire:model="vacationBlackoutForm.start_date" 
                    :label="__('Start Date')" 
                    required 
                />
                
                <x-date 
                    wire:model="vacationBlackoutForm.end_date" 
                    :label="__('End Date')" 
                    required 
                />
            </div>
            
            <x-textarea 
                wire:model="vacationBlackoutForm.description" 
                :label="__('Description')" 
                rows="3"
            />
            
            <x-select.styled
                wire:model="vacationBlackoutForm.role_ids"
                :label="__('Applies to Roles')"
                :placeholder="__('Select Roles')"
                multiple
                select="label:name|value:id"
                :request="['url' => route('search', \FluxErp\Models\Role::class), 'method' => 'POST']"
                unfiltered
            />
            
            <x-select.styled
                wire:model="vacationBlackoutForm.user_ids"
                :label="__('Applies to Users')"
                :placeholder="__('Select Users')"
                :hint="__('Leave empty to apply to all users with selected roles')"
                multiple
                select="label:name|value:id"
                :request="['url' => route('search', \FluxErp\Models\User::class), 'method' => 'POST']"
                unfiltered
            />
            
            <x-checkbox 
                wire:model="vacationBlackoutForm.is_active" 
                :label="__('Is Active')" 
            />
        </div>
        
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('{{ $vacationBlackoutForm->modalName() }}')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                x-on:click="$wire.save().then((success) => { if(success) $modalClose('{{ $vacationBlackoutForm->modalName() }}')})"
            />
        </x-slot>
    </x-modal>
</div>