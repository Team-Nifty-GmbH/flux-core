<div>
    <div class="flex justify-between items-center mb-4">
        <x-tabs wire:model.live="viewType">
            <x-tab name="my" :label="__('My Requests')" />
            <x-tab name="team" :label="__('Team Requests')" />
            <x-tab name="approval" :label="__('Pending Approvals')" />
            <x-tab name="all" :label="__('All Requests')" />
        </x-tabs>
        
        <div class="flex items-center gap-4">
            <div class="text-sm text-gray-600">
                <span>{{ __('Vacation Balance') }}:</span>
                <span class="font-medium">{{ $this->vacationBalance['remaining'] }}/{{ $this->vacationBalance['total'] }}</span>
                <span x-show="$wire.vacationBalance.pending > 0" class="text-yellow-600">
                    (<span x-text="$wire.vacationBalance.pending"></span> {{ __('pending') }})
                </span>
            </div>
        </div>
    </div>
    
    <x-modal id="edit-vacation-request-modal" width="3xl">
        <x-slot:title>
            <span x-text="$wire.vacationRequestForm.id ? '{{ __('Edit Vacation Request') }}' : '{{ __('New Vacation Request') }}'"></span>
        </x-slot:title>
        
        <div class="flex flex-col gap-4">
            <x-select.styled
                wire:model="vacationRequestForm.user_id"
                :label="__('Employee')"
                :request="['url' => route('search', \FluxErp\Models\User::class), 'method' => 'POST']"
                select="label:name|value:id"
                unfiltered
            />
            
            <x-select.styled
                wire:model="vacationRequestForm.work_time_category_id"
                :label="__('Vacation Type')"
                :request="['url' => route('search', \FluxErp\Models\WorkTimeCategory::class), 'method' => 'POST']"
                select="label:name|value:id"
                unfiltered
                required
            />
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-date
                        wire:model="vacationRequestForm.start_date"
                        :label="__('Start Date')"
                        required
                    />
                    <x-select.styled
                        wire:model="vacationRequestForm.start_half_day"
                        :label="__('Start')"
                        :options="[
                            ['value' => 'full', 'label' => __('Full Day')],
                            ['value' => 'morning', 'label' => __('Morning Only')],
                            ['value' => 'afternoon', 'label' => __('Afternoon Only')]
                        ]"
                        select="label:label|value:value"
                    />
                </div>
                
                <div>
                    <x-date
                        wire:model="vacationRequestForm.end_date"
                        :label="__('End Date')"
                        required
                    />
                    <x-select.styled
                        wire:model="vacationRequestForm.end_half_day"
                        :label="__('End')"
                        :options="[
                            ['value' => 'full', 'label' => __('Full Day')],
                            ['value' => 'morning', 'label' => __('Morning Only')],
                            ['value' => 'afternoon', 'label' => __('Afternoon Only')]
                        ]"
                        select="label:label|value:value"
                    />
                </div>
            </div>
            
            <x-select.styled
                wire:model="vacationRequestForm.substitute_user_id"
                :label="__('Substitute')"
                :request="['url' => route('search', \FluxErp\Models\User::class), 'method' => 'POST']"
                select="label:name|value:id"
                unfiltered
                :hint="__('Who will cover your responsibilities')"
            />
            
            <x-textarea
                wire:model="vacationRequestForm.reason"
                :label="__('Reason')"
                rows="3"
            />
            
            <x-checkbox
                wire:model="vacationRequestForm.is_emergency"
                :label="__('Emergency Request')"
                :hint="__('Check if this is an urgent/emergency request')"
            />
            
            <div x-show="$wire.vacationRequestForm.days_requested" class="p-3 bg-gray-50 rounded">
                <span class="text-sm text-gray-600">{{ __('Days Requested') }}:</span>
                <span class="font-medium" x-text="$wire.vacationRequestForm.days_requested"></span>
            </div>
        </div>
        
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('edit-vacation-request-modal')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                x-on:click="$wire.save().then((success) => { if(success) $modalClose('edit-vacation-request-modal')})"
            />
        </x-slot:footer>
    </x-modal>
    
    <x-modal id="view-vacation-request-modal" width="3xl">
        <x-slot:title>
            {{ __('Vacation Request Details') }}
        </x-slot:title>
        
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-600">{{ __('Employee') }}</label>
                    <p class="font-medium">{{ $viewingRequest?->user?->name }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">{{ __('Status') }}</label>
                    <p class="font-medium">
                        <x-badge :color="match($vacationRequestForm->status) {
                            'draft' => 'gray',
                            'pending' => 'yellow',
                            'approved' => 'green',
                            'rejected' => 'red',
                            'cancelled' => 'gray',
                            default => 'gray'
                        }">
                            {{ __($vacationRequestForm->status) }}
                        </x-badge>
                    </p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-600">{{ __('Start Date') }}</label>
                    <p class="font-medium">{{ $vacationRequestForm->start_date }} ({{ __($vacationRequestForm->start_half_day) }})</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">{{ __('End Date') }}</label>
                    <p class="font-medium">{{ $vacationRequestForm->end_date }} ({{ __($vacationRequestForm->end_half_day) }})</p>
                </div>
            </div>
            
            <div>
                <label class="text-sm text-gray-600">{{ __('Days Requested') }}</label>
                <p class="font-medium">{{ $vacationRequestForm->days_requested }}</p>
            </div>
            
            @if($vacationRequestForm->substitute_user_id)
                <div>
                    <label class="text-sm text-gray-600">{{ __('Substitute') }}</label>
                    <p class="font-medium">{{ $viewingRequest?->substituteUser?->name }}</p>
                </div>
            @endif
            
            @if($vacationRequestForm->reason)
                <div>
                    <label class="text-sm text-gray-600">{{ __('Reason') }}</label>
                    <p class="font-medium">{{ $vacationRequestForm->reason }}</p>
                </div>
            @endif
            
            @if($vacationRequestForm->approved_by)
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">{{ __('Approved By') }}</label>
                        <p class="font-medium">{{ $viewingRequest?->approvedBy?->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">{{ __('Approved At') }}</label>
                        <p class="font-medium">{{ $vacationRequestForm->approved_at }}</p>
                    </div>
                </div>
                
                @if($vacationRequestForm->approval_note)
                    <div>
                        <label class="text-sm text-gray-600">{{ __('Approval Note') }}</label>
                        <p class="font-medium">{{ $vacationRequestForm->approval_note }}</p>
                    </div>
                @endif
                
                @if($vacationRequestForm->rejection_reason)
                    <div>
                        <label class="text-sm text-gray-600">{{ __('Rejection Reason') }}</label>
                        <p class="font-medium text-red-600">{{ $vacationRequestForm->rejection_reason }}</p>
                    </div>
                @endif
            @endif
        </div>
        
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Close')"
                x-on:click="$modalClose('view-vacation-request-modal')"
            />
        </x-slot:footer>
    </x-modal>
    
    <x-modal id="approve-vacation-request-modal" width="2xl">
        <x-slot:title>
            {{ __('Approve Vacation Request') }}
        </x-slot:title>
        
        <div class="space-y-4">
            <div class="p-3 bg-blue-50 rounded">
                <p class="text-sm">
                    {{ __('Approving vacation request for') }}
                    <span class="font-medium">{{ $viewingRequest?->user?->name ?? __('Employee') }}</span>
                    {{ __('from') }}
                    <span class="font-medium">{{ $vacationRequestForm->start_date }}</span>
                    {{ __('to') }}
                    <span class="font-medium">{{ $vacationRequestForm->end_date }}</span>
                    ({{ $vacationRequestForm->days_requested }} {{ __('days') }})
                </p>
            </div>
            
            <x-textarea
                wire:model="approvalNote"
                :label="__('Approval Note (Optional)')"
                rows="3"
            />
        </div>
        
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('approve-vacation-request-modal')"
            />
            <x-button
                color="green"
                :text="__('Approve')"
                wire:click="approve().then((success) => { if(success) $modalClose('approve-vacation-request-modal')})"
            />
        </x-slot:footer>
    </x-modal>
    
    <x-modal id="reject-vacation-request-modal" width="2xl">
        <x-slot:title>
            {{ __('Reject Vacation Request') }}
        </x-slot:title>
        
        <div class="space-y-4">
            <div class="p-3 bg-red-50 rounded">
                <p class="text-sm">
                    {{ __('Rejecting vacation request for') }}
                    <span class="font-medium">{{ $viewingRequest?->user?->name ?? __('Employee') }}</span>
                    {{ __('from') }}
                    <span class="font-medium">{{ $vacationRequestForm->start_date }}</span>
                    {{ __('to') }}
                    <span class="font-medium">{{ $vacationRequestForm->end_date }}</span>
                    ({{ $vacationRequestForm->days_requested }} {{ __('days') }})
                </p>
            </div>
            
            <x-textarea
                wire:model="rejectionReason"
                :label="__('Rejection Reason')"
                rows="3"
                required
            />
        </div>
        
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('reject-vacation-request-modal')"
            />
            <x-button
                color="red"
                :text="__('Reject')"
                wire:click="reject().then((success) => { if(success) $modalClose('reject-vacation-request-modal')})"
            />
        </x-slot:footer>
    </x-modal>
</div>