<div>
    <x-modal :id="$vacationBlackoutForm->modalName()" size="2xl">
        <x-slot:title>
            {{ $vacationBlackoutForm->id ? __('Edit Vacation Blackout') : __('Create Vacation Blackout') }}
        </x-slot:title>
        
        <div class="flex flex-col gap-4">
            @if(resolve_static(\FluxErp\Models\Client::class, 'query')->count() > 1)
                <x-select.styled
                    wire:model="vacationBlackoutForm.client_id"
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
                :request="[
                    'url' => route('search', \FluxErp\Models\Role::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => ['name']
                    ]
                ]"
                unfiltered
            />
            
            <x-select.styled
                wire:model="vacationBlackoutForm.employee_ids"
                :label="__('Applies to Employees')"
                :placeholder="__('Select Employees')"
                :hint="__('Leave empty to apply to all employees with selected roles')"
                multiple
                select="label:name|value:id"
                :request="[
                    'url' => route('search', \FluxErp\Models\Employee::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => ['name', 'email']
                    ]
                ]"
                unfiltered
            />
            
            <x-toggle 
                wire:model="vacationBlackoutForm.is_active" 
                :label="__('Is Active')" 
            />
        </div>
        
        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$modalClose('{{ $vacationBlackoutForm->modalName() }}')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                wire:click="save"
            />
        </x-slot:footer>
    </x-modal>
</div>