<div>
    @use(Illuminate\Support\Number)
    <div class="mx-auto md:flex md:items-center md:justify-between md:space-x-5">
        <div class="flex items-center space-x-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    {{ __('Employee Day #:id', ['id' => $employeeDayForm->id]) }}
                </h1>
                <div class="flex items-center gap-4 mt-2">
                   <div class="text-sm text-gray-500 dark:text-gray-400">
                        <x-link
                            :href="route('human-resources.employees.id', ['id' => $employeeDayForm->employee_id])"
                            :text="__('Employee') . ': ' . data_get($employeeDayForm, 'employee.name')"
                        />
                   </div>

                    @if(bccomp($plusMinusOvertime = data_get($this->employeeDayForm, 'plus_minus_overtime_hours', 0), 0) === 1)
                        <x-badge
                            color="emerald"
                            :text="__('Overtime') . ': ' . Number::format($plusMinusOvertime) . 'h'"
                        />
                    @elseif(bccomp($plusMinusOvertime, 0) === -1)
                        <x-badge
                            color="red"
                            :text="__('Deficit') . ': ' . Number::format($plusMinusOvertime) . 'h'"
                        />
                    @else
                        <x-badge
                            color="amber"
                            :text="__('Balanced') . ': ' . Number::format($plusMinusOvertime) . 'h'"
                        />
                    @endif

                    @if(data_get($employeeDayForm, 'is_holiday'))
                        <x-badge color="blue" :text="__('Holiday')" />
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-flux::tabs wire:model.live="tab" :$tabs />
</div>
