<div x-data="{
    days: ['{{ __('Mon') }}', '{{ __('Tue') }}', '{{ __('Wed') }}', '{{ __('Thu') }}', '{{ __('Fri') }}', '{{ __('Sat') }}', '{{ __('Sun') }}']
}">
    <x-modal :id="$workTimeModelForm->modalName()" size="4xl">
        <x-slot:title>
            <span x-text="$wire.workTimeModelForm.id ? '{{ __('Edit Work Time Model') }}' : '{{ __('Create Work Time Model') }}'"></span>
        </x-slot:title>
        
        <div class="flex flex-col gap-4">
            <x-input wire:model="workTimeModelForm.name" :label="__('Name')" required />
            
            <div class="grid grid-cols-2 gap-4">
                <x-input 
                    wire:model.live="workTimeModelForm.cycle_weeks" 
                    type="number" 
                    min="1" 
                    max="52"
                    :label="__('Cycle Weeks')" 
                    :hint="__('Number of weeks in the cycle')"
                    required 
                />
                
                <x-input 
                    wire:model="workTimeModelForm.weekly_hours" 
                    type="number" 
                    step="0.5"
                    min="0"
                    max="168"
                    :label="__('Weekly Hours')" 
                    required 
                />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <x-input 
                    wire:model="workTimeModelForm.annual_vacation_days" 
                    type="number" 
                    min="0"
                    max="365"
                    :label="__('Annual Vacation Days')" 
                    required 
                />
                
                <x-input 
                    wire:model="workTimeModelForm.max_overtime_hours" 
                    type="number" 
                    step="0.5"
                    min="0"
                    :label="__('Max Overtime Hours')" 
                />
            </div>
            
            <x-select.styled
                wire:model="workTimeModelForm.overtime_compensation"
                :label="__('Overtime Compensation')"
                :options="[
                    ['value' => 'time_off', 'label' => __('Time Off')],
                    ['value' => 'payment', 'label' => __('Payment')],
                    ['value' => 'both', 'label' => __('Both')]
                ]"
                select="label:label|value:value"
                required
            />
            
            <div class="space-y-2">
                <x-checkbox 
                    wire:model.live="workTimeModelForm.has_core_hours" 
                    :label="__('Has Core Hours')" 
                />
                
                <div x-show="$wire.workTimeModelForm.has_core_hours" class="grid grid-cols-2 gap-4 pl-6">
                    <x-input 
                        wire:model="workTimeModelForm.core_hours_start" 
                        type="time"
                        :label="__('Core Hours Start')" 
                        required 
                    />
                    
                    <x-input 
                        wire:model="workTimeModelForm.core_hours_end" 
                        type="time"
                        :label="__('Core Hours End')" 
                        required 
                    />
                </div>
            </div>
            
            <x-checkbox 
                wire:model="workTimeModelForm.is_active" 
                :label="__('Is Active')" 
            />
            
            {{-- Schedule Configuration --}}
            <div class="border-t pt-4">
                <h3 class="font-medium mb-3">{{ __('Work Schedule') }}</h3>
                
                <template x-for="(week, weekIndex) in $wire.workTimeModelForm.schedules" :key="weekIndex">
                    <div>
                        <div x-show="$wire.workTimeModelForm.cycle_weeks > 1" class="text-sm font-medium text-gray-600 mb-2">
                            {{ __('Week') }} <span x-text="week.week_number"></span>
                        </div>
                        
                        <div class="grid grid-cols-8 gap-2 text-xs">
                            <div class="font-medium py-2">{{ __('Day') }}</div>
                            <template x-for="day in days" :key="day">
                                <div class="text-center" x-text="day"></div>
                            </template>
                            
                            <div class="font-medium py-1">{{ __('Start') }}</div>
                            <template x-for="dayNum in [1,2,3,4,5,6,7]" :key="'start-' + weekIndex + '-' + dayNum">
                                <input 
                                    type="time" 
                                    x-model="$wire.workTimeModelForm.schedules[weekIndex].days[dayNum].start_time"
                                    class="px-1 py-1 text-xs border rounded"
                                />
                            </template>
                            
                            <div class="font-medium py-1">{{ __('End') }}</div>
                            <template x-for="dayNum in [1,2,3,4,5,6,7]" :key="'end-' + weekIndex + '-' + dayNum">
                                <input 
                                    type="time" 
                                    x-model="$wire.workTimeModelForm.schedules[weekIndex].days[dayNum].end_time"
                                    class="px-1 py-1 text-xs border rounded"
                                />
                            </template>
                            
                            <div class="font-medium py-1">{{ __('Hours') }}</div>
                            <template x-for="dayNum in [1,2,3,4,5,6,7]" :key="'hours-' + weekIndex + '-' + dayNum">
                                <input 
                                    type="number" 
                                    step="0.5"
                                    min="0"
                                    max="24"
                                    x-model="$wire.workTimeModelForm.schedules[weekIndex].days[dayNum].work_hours"
                                    class="px-1 py-1 text-xs border rounded text-center"
                                />
                            </template>
                            
                            <div class="font-medium py-1">{{ __('Break (min)') }}</div>
                            <template x-for="dayNum in [1,2,3,4,5,6,7]" :key="'break-' + weekIndex + '-' + dayNum">
                                <input 
                                    type="number" 
                                    step="15"
                                    min="0"
                                    max="480"
                                    x-model="$wire.workTimeModelForm.schedules[weekIndex].days[dayNum].break_minutes"
                                    class="px-1 py-1 text-xs border rounded text-center"
                                />
                            </template>
                        </div>
                        
                        <div x-show="weekIndex < $wire.workTimeModelForm.schedules.length - 1" class="border-b my-3"></div>
                    </div>
                </template>
            </div>
        </div>
    
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('{{ $workTimeModelForm->modalName() }}')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                wire:click="save"
                x-on:click="$wire.save().then((success) => { if(success) $modalClose('{{ $workTimeModelForm->modalName() }}')})"
            />
        </x-slot>
    </x-modal>
</div>