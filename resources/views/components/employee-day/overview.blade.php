<div>
    @use(Illuminate\Support\Number)
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card :header="__('Basic Information')">
            <div class="space-y-4">
                <div>
                    <x-input
                        :label="__('Employee')"
                        wire:model="employeeDayForm.employee.name"
                        disabled
                        class="mt-1"
                    />
                </div>

                <div class="pointer-events-none">
                    <x-date
                        wire:model="employeeDayForm.date"
                        x-bind:disabled="true"
                        :label="__('Date')"
                    />
                </div>

                <div>
                    <x-number
                        :label="__('Target Hours')"
                        wire:model="employeeDayForm.target_hours"
                        disabled
                        class="mt-1"
                    />
                </div>

                <div>
                    <x-number
                        :label="__('Actual Hours')"
                        wire:model="employeeDayForm.actual_hours"
                        disabled
                        class="mt-1"
                    />
                </div>
            </div>
        </x-card>

        <x-card :header="__('Time Breakdown')">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm">{{ __('Target Hours') }}</span>
                    <div
                        class="text-2xl font-bold text-green-600 dark:text-green-400"
                    >
                        {{ Number::format(data_get($this->employeeDayForm, 'target_hours', 0)) . 'h' }}
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm">{{ __('Actual Work Time') }}</span>
                    <div class="font-medium text-green-600 dark:text-green-400">
                        {{ Number::format(data_get($this->employeeDayForm, 'actual_hours', 0)) . 'h' }}
                    </div>
                </div>

                <div class="flex items-center justify-between opacity-50">
                    <span class="text-sm">{{ __('Break Time') }}</span>
                    <span
                        class="font-medium text-yellow-600 dark:text-yellow-400"
                    >
                        {{ Number::format(bcdiv(data_get($this->employeeDayForm, 'break_minutes') ?? 0, 60)) . 'h' }}
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm">
                        {{ __('Plus Minus Absence Hours') }}
                    </span>
                    <span
                        class="font-medium text-orange-600 dark:text-orange-400"
                    >
                        {{ Number::format(data_get($this->employeeDayForm, 'plus_minus_absence_hours', 0)) . 'h' }}
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm">{{ __('Sick Hours Used') }}</span>
                    <span class="font-medium text-red-600 dark:text-red-400">
                        {{ Number::format(data_get($this->employeeDayForm, 'sick_hours_used', 0)) . 'h' }}
                        ({{ Number::format(data_get($this->employeeDayForm, 'sick_days_used', 0)) . ' ' . __('Days') }})
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm">
                        {{ __('Vacation Hours Used') }}
                    </span>
                    <span class="font-medium text-red-600 dark:text-red-400">
                        {{ Number::format(data_get($this->employeeDayForm, 'vacation_hours_used', 0)) . 'h' }}
                        ({{ Number::format(data_get($this->employeeDayForm, 'vacation_days_used', 0)) . ' ' . __('Days') }})
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm">
                        {{ __('Plus Minus Overtime Hours') }}
                    </span>
                    <span
                        class="{{ bccomp(data_get($this->employeeDayForm, 'plus_minus_overtime_hours') ?? 0, 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} text-2xl font-bold"
                    >
                        {{ Number::format(data_get($this->employeeDayForm, 'plus_minus_overtime_hours', 0)) . 'h' }}
                    </span>
                </div>
            </div>
        </x-card>
    </div>
</div>
