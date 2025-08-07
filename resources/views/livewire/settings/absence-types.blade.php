<div x-data>
    <x-modal :id="$absenceTypeForm->modalName()" width="3xl">
        <x-slot:title>
            <span x-text="$wire.absenceTypeForm.id ? '{{ __('Edit Absence Type') }}' : '{{ __('Create Absence Type') }}'"></span>
        </x-slot:title>
    
    <div class="flex flex-col gap-4">
        <div class="grid grid-cols-2 gap-4">
            <x-input wire:model="absenceTypeForm.name" :label="__('Name')" required />
            <x-color wire:model="absenceTypeForm.color" :label="__('Color')" required />
        </div>
        
        <div class="border rounded-lg p-4">
            <h3 class="font-medium mb-3">{{ __('Substitute Settings') }}</h3>
            <div class="space-y-2">
                <x-checkbox 
                    wire:model="absenceTypeForm.can_select_substitute" 
                    :label="__('Can Select Substitute')" 
                />
                <x-checkbox 
                    wire:model="absenceTypeForm.must_select_substitute" 
                    :label="__('Must Select Substitute')" 
                />
            </div>
        </div>
        
        <div class="border rounded-lg p-4">
            <h3 class="font-medium mb-3">{{ __('Requirements') }}</h3>
            <div class="space-y-2">
                <x-checkbox 
                    wire:model="absenceTypeForm.requires_proof" 
                    :label="__('Requires Proof Document')" 
                />
                <x-checkbox 
                    wire:model="absenceTypeForm.requires_reason" 
                    :label="__('Requires Reason')" 
                />
                <x-checkbox 
                    wire:model="absenceTypeForm.requires_work_day" 
                    :label="__('Can Only Be Used on Work Days')" 
                />
            </div>
        </div>
        
        <x-select.styled
            wire:model="absenceTypeForm.employee_can_create"
            :label="__('Employee Can Create')"
            :options="[
                ['value' => 'yes', 'label' => __('Yes')],
                ['value' => 'no', 'label' => __('No')],
                ['value' => 'approval_required', 'label' => __('Approval Required')]
            ]"
            select="label:label|value:value"
            required
        />
        
        <div class="border rounded-lg p-4">
            <h3 class="font-medium mb-3">{{ __('Time Calculation') }}</h3>
            <div class="space-y-2">
                <x-checkbox 
                    wire:model="absenceTypeForm.counts_as_work_day" 
                    :label="__('Counts as Work Day')" 
                />
                <x-checkbox 
                    wire:model="absenceTypeForm.counts_as_target_hours" 
                    :label="__('Counts as Target Hours')" 
                />
                <x-checkbox 
                    wire:model="absenceTypeForm.is_vacation" 
                    :label="__('Is Vacation Type')" 
                />
            </div>
        </div>
        
        <x-checkbox 
            wire:model="absenceTypeForm.is_active" 
            :label="__('Is Active')" 
        />
    </div>
    
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose($wire.absenceTypeForm.modalName())"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="$wire.save().then((success) => { if(success) $modalClose($wire.absenceTypeForm.modalName())})"
        />
    </x-slot>
    </x-modal>
</div>