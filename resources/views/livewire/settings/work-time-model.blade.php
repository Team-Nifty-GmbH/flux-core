<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    {{ $workTimeModelForm->name ?? __('New Work Time Model') }}
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Configure the work schedule and settings for this work time model') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-button
                    :text="__('Delete')"
                    color="red"
                    wire:click="delete"
                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Work Time Model')]) }}"
                />
                <x-button
                    :text="__('Save')"
                    color="primary"
                    wire:click="save"
                    wire:loading.attr="disabled"
                />
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Basic Settings --}}
        <x-card :header="__('Basic Settings')">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input
                        wire:model="workTimeModelForm.name"
                        :label="__('Name')"
                        required
                    />
                </div>

                <x-number
                    wire:model.live="workTimeModelForm.cycle_weeks"
                    x-on:change="$wire.dispatch('cycle-weeks-updated')"
                    :label="__('Cycle Weeks')"
                    :hint="__('Number of weeks before the schedule repeats')"
                    min="1"
                    max="12"
                    required
                />

                <x-number
                    wire:model="workTimeModelForm.weekly_hours"
                    :label="__('Weekly Hours')"
                    :hint="__('Standard working hours per week')"
                    step="0.5"
                    min="0"
                    max="60"
                    required
                />

                <x-number
                    wire:model="workTimeModelForm.annual_vacation_days"
                    :label="__('Annual Vacation Days')"
                    :hint="__('Total vacation days per year')"
                    min="0"
                    max="365"
                    step="0.5"
                    required
                />

                <x-number
                    wire:model="workTimeModelForm.work_days_per_week"
                    :label="__('Work Days Per Week')"
                    :hint="__('Number of working days in a standard week')"
                    min="1"
                    max="7"
                    step="1"
                />

                <x-number
                    wire:model="workTimeModelForm.max_overtime_hours"
                    :label="__('Max Overtime Hours')"
                    step="0.5"
                    min="0"
                />

                <x-select.styled
                    wire:model="workTimeModelForm.overtime_compensation"
                    :label="__('Overtime Compensation')"
                    select="label:label|value:value"
                    :options="\FluxErp\Enums\OvertimeCompensationEnum::valuesLocalized()"
                />

                <div class="sm:col-span-2">
                    <x-toggle
                        wire:model="workTimeModelForm.is_active"
                        :label="__('Active')"
                    />
                </div>
            </div>
        </x-card>

        {{-- Work Schedule Configuration --}}
        <x-card :header="__('Weekly Schedule')">
            <div
                x-data="{
                    activeWeek: 0,
                    weekdays: {
                        1: '{{ __('Monday') }}',
                        2: '{{ __('Tuesday') }}',
                        3: '{{ __('Wednesday') }}',
                        4: '{{ __('Thursday') }}',
                        5: '{{ __('Friday') }}',
                        6: '{{ __('Saturday') }}',
                        7: '{{ __('Sunday') }}',
                    },
                }"
            >
                @if ($workTimeModelForm->cycle_weeks > 1)
                    {{-- Tabs for multiple weeks --}}
                    <div class="mb-6">
                        <div
                            class="border-b border-gray-200 dark:border-gray-700"
                        >
                            <nav class="-mb-px flex space-x-4">
                                @for ($i = 0; $i < $workTimeModelForm->cycle_weeks; $i++)
                                    <button
                                        type="button"
                                        x-on:click="activeWeek = {{ $i }}"
                                        x-bind:class="
                                            activeWeek === {{ $i }}
                                                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                        "
                                        class="whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium transition-colors"
                                    >
                                        {{ __('Week :number', ['number' => $i + 1]) }}
                                    </button>
                                @endfor
                            </nav>
                        </div>
                    </div>
                @endif

                {{-- Schedule Table for every single week --}}
                <template
                    x-for="(week, weekIndex) in $wire.workTimeModelForm.schedules"
                    :key="weekIndex"
                >
                    <div
                        x-show="activeWeek === weekIndex || $wire.workTimeModelForm.cycle_weeks === 1"
                        x-cloak
                    >
                        <div class="overflow-x-auto">
                            <table
                                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                        >
                                            {{ __('Day') }}
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                        >
                                            {{ __('Start Time') }}
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                        >
                                            {{ __('End Time') }}
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                        >
                                            {{ __('Break (Minutes)') }}
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                        >
                                            {{ __('Work Hours') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900"
                                >
                                    <template
                                        x-for="(dayName, dayNumber) in weekdays"
                                        :key="dayNumber"
                                    >
                                        <tr>
                                            <td
                                                class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                                                x-text="dayName"
                                            ></td>
                                            <td
                                                class="whitespace-nowrap px-6 py-4"
                                            >
                                                <x-input
                                                    type="time"
                                                    x-model="$wire.workTimeModelForm.schedules[weekIndex].days[dayNumber].start_time"
                                                    x-on:change="$wire.updateSchedule(weekIndex, dayNumber, 'start_time', $event.target.value)"
                                                    class="!py-1"
                                                />
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-6 py-4"
                                            >
                                                <x-input
                                                    type="time"
                                                    x-model="$wire.workTimeModelForm.schedules[weekIndex].days[dayNumber].end_time"
                                                    x-on:change="$wire.updateSchedule(weekIndex, dayNumber, 'end_time', $event.target.value)"
                                                    class="!py-1"
                                                />
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-6 py-4"
                                            >
                                                <x-number
                                                    x-model="$wire.workTimeModelForm.schedules[weekIndex].days[dayNumber].break_minutes"
                                                    x-on:change="$wire.updateSchedule(weekIndex, dayNumber, 'break_minutes', $event.target.value)"
                                                    min="0"
                                                    max="480"
                                                    step="15"
                                                    class="!py-1"
                                                />
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100"
                                            >
                                                <span
                                                    class="font-medium"
                                                    x-html="
                                                        parseFloat(
                                                            $wire.workTimeModelForm.schedules[weekIndex].days[dayNumber]?.work_hours ??
                                                                '0.00',
                                                        ).toFixed(2)
                                                    "
                                                ></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </x-card>
    </div>
</div>
