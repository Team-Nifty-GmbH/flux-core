<div x-data>
    <x-modal :id="$userForm->modalName()" width="3xl">
        <x-slot:title>
            {{ __('Edit HR Settings') }}
        </x-slot:title>
    
    <div class="flex flex-col gap-4">
        <div class="border rounded-lg p-4">
            <h3 class="font-medium mb-3">{{ __('Employment Information') }}</h3>
            <div class="grid grid-cols-2 gap-4">
                <x-date 
                    wire:model="userForm.employment_date" 
                    :label="__('Employment Date')" 
                />
                
                <x-date 
                    wire:model="userForm.termination_date" 
                    :label="__('Termination Date')" 
                />
                
                <x-select.styled
                    wire:model="userForm.work_time_model_id"
                    :label="__('Work Time Model')"
                    select="label:name|value:id"
                    :request="['url' => route('search', \FluxErp\Models\WorkTimeModel::class), 'method' => 'POST']"
                    unfiltered
                />
                
                <x-select.styled
                    wire:model="userForm.location_id"
                    :label="__('Location')"
                    select="label:name|value:id"
                    :request="['url' => route('search', \FluxErp\Models\Location::class), 'method' => 'POST']"
                    unfiltered
                />
                
                <x-select.styled
                    wire:model="userForm.supervisor_id"
                    :label="__('Supervisor')"
                    select="label:name|value:id"
                    :request="['url' => route('search', \FluxErp\Models\User::class), 'method' => 'POST']"
                    unfiltered
                />
            </div>
        </div>
        
        <div class="border rounded-lg p-4">
            <h3 class="font-medium mb-3">{{ __('Personal Information') }}</h3>
            <div class="grid grid-cols-2 gap-4">
                <x-date 
                    wire:model="userForm.birth_date" 
                    :label="__('Birth Date')" 
                />
                
                <x-input 
                    wire:model="userForm.social_security_number" 
                    :label="__('Social Security Number')" 
                />
                
                <x-input 
                    wire:model="userForm.tax_id" 
                    :label="__('Tax ID')" 
                />
                
                <x-input 
                    wire:model="userForm.tax_class" 
                    :label="__('Tax Class')" 
                />
            </div>
        </div>
        
        <div class="border rounded-lg p-4">
            <h3 class="font-medium mb-3">{{ __('Compensation') }}</h3>
            <div class="grid grid-cols-2 gap-4">
                <x-input 
                    wire:model="userForm.salary" 
                    :label="__('Salary')" 
                    type="number" 
                    step="0.01" 
                />
                
                <x-select.styled
                    wire:model="userForm.salary_type"
                    :label="__('Salary Type')"
                    :options="[
                        ['value' => 'hourly', 'label' => __('Hourly')],
                        ['value' => 'monthly', 'label' => __('Monthly')],
                        ['value' => 'yearly', 'label' => __('Yearly')]
                    ]"
                    select="label:label|value:value"
                />
            </div>
        </div>
        
        <div class="border rounded-lg p-4">
            <h3 class="font-medium mb-3">{{ __('Time & Vacation') }}</h3>
            <div class="grid grid-cols-3 gap-4">
                <x-input 
                    wire:model="userForm.vacation_days_current" 
                    :label="__('Current Vacation Days')" 
                    type="number" 
                    step="0.5" 
                />
                
                <x-input 
                    wire:model="userForm.vacation_days_carried" 
                    :label="__('Carried Vacation Days')" 
                    type="number" 
                    step="0.5" 
                />
                
                <x-input 
                    wire:model="userForm.overtime_hours" 
                    :label="__('Overtime Hours')" 
                    type="number" 
                    step="0.25" 
                />
            </div>
        </div>
        
        <div class="border rounded-lg p-4">
            <h3 class="font-medium mb-3">{{ __('Emergency Contact') }}</h3>
            <div class="grid grid-cols-2 gap-4">
                <x-input 
                    wire:model="userForm.emergency_contact_name" 
                    :label="__('Contact Name')" 
                />
                
                <x-input 
                    wire:model="userForm.emergency_contact_phone" 
                    :label="__('Contact Phone')" 
                />
                
                <x-input 
                    wire:model="userForm.emergency_contact_relation" 
                    :label="__('Relationship')" 
                    class="col-span-2"
                />
            </div>
        </div>
    </div>
    
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose($wire.userForm.modalName())"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="$wire.save().then((success) => { if(success) $modalClose($wire.userForm.modalName())})"
        />
    </x-slot>
    </x-modal>
</div>