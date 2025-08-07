<x-modal id="edit-absence-request-modal" width="3xl">
    <x-slot:title>
        {{ __('Absence Request') }}
    </x-slot:title>
    
    <div class="flex flex-col gap-4">
        <div class="grid grid-cols-2 gap-4">
            <x-select.styled
                wire:model="absenceRequestForm.user_id"
                :label="__('Employee')"
                select="label:name|value:id"
                :request="['url' => route('search', \FluxErp\Models\User::class), 'method' => 'POST']"
                unfiltered
                required
            />
            
            <x-select.styled
                wire:model="absenceRequestForm.absence_type_id"
                :label="__('Absence Type')"
                select="label:name|value:id"
                :request="['url' => route('search', \FluxErp\Models\AbsenceType::class), 'method' => 'POST']"
                unfiltered
                required
            />
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <x-date 
                wire:model="absenceRequestForm.start_date" 
                :label="__('Start Date')" 
                required
            />
            
            <x-date 
                wire:model="absenceRequestForm.end_date" 
                :label="__('End Date')" 
                required
            />
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <x-select.styled
                wire:model="absenceRequestForm.start_half_day"
                :label="__('Start Half Day')"
                :options="[
                    ['value' => 'full', 'label' => __('Full Day')],
                    ['value' => 'first_half', 'label' => __('Morning')],
                    ['value' => 'second_half', 'label' => __('Afternoon')]
                ]"
                select="label:label|value:value"
            />
            
            <x-select.styled
                wire:model="absenceRequestForm.end_half_day"
                :label="__('End Half Day')"
                :options="[
                    ['value' => 'full', 'label' => __('Full Day')],
                    ['value' => 'first_half', 'label' => __('Morning')],
                    ['value' => 'second_half', 'label' => __('Afternoon')]
                ]"
                select="label:label|value:value"
            />
        </div>
        
        <x-input 
            wire:model="absenceRequestForm.days_requested" 
            :label="__('Days Requested')" 
            type="number"
            step="0.5"
        />
        
        <x-textarea 
            wire:model="absenceRequestForm.reason" 
            :label="__('Reason')" 
            rows="3"
        />
        
        <x-select.styled
            wire:model="absenceRequestForm.substitute_user_id"
            :label="__('Substitute')"
            select="label:name|value:id"
            :request="['url' => route('search', \FluxErp\Models\User::class), 'method' => 'POST']"
            unfiltered
        />
        
        <x-textarea 
            wire:model="absenceRequestForm.substitute_note" 
            :label="__('Note for Substitute')" 
            rows="2"
        />
        
        <div class="grid grid-cols-2 gap-4">
            <x-select.styled
                wire:model="absenceRequestForm.status"
                :label="__('Status')"
                :options="[
                    ['value' => 'draft', 'label' => __('Draft')],
                    ['value' => 'pending', 'label' => __('Pending')],
                    ['value' => 'approved', 'label' => __('Approved')],
                    ['value' => 'rejected', 'label' => __('Rejected')],
                    ['value' => 'cancelled', 'label' => __('Cancelled')]
                ]"
                select="label:label|value:value"
            />
            
            <x-checkbox 
                wire:model="absenceRequestForm.is_emergency" 
                :label="__('Emergency Request')" 
            />
        </div>
    </div>
    
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-absence-request-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-absence-request-modal')})"
        />
    </x-slot>
</x-modal>