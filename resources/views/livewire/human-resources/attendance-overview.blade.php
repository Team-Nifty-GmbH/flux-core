<div
    class="flex h-full flex-col"
    x-data="{
        expandedDepartments: {},
        hiddenStatuses: {},
        absenceTypes: @js($absenceTypes),
        departments: @js($departments),

        toggleDepartment(deptId) {
            if (this.expandedDepartments[deptId] === undefined) {
                this.expandedDepartments[deptId] = true
            }
            this.expandedDepartments[deptId] = ! this.expandedDepartments[deptId]
        },

        isDepartmentExpanded(deptId) {
            return this.expandedDepartments[deptId] !== false
        },

        toggleStatus(status) {
            this.hiddenStatuses[status] = ! this.hiddenStatuses[status]
        },

        isStatusHidden(status) {
            return this.hiddenStatuses[status] === true
        },

        getDayClass(calDay) {
            if (calDay.isToday) {
                return 'bg-yellow-100 dark:bg-yellow-900 border-l-2 border-r-2 border-t-2 border-yellow-400 dark:border-yellow-600'
            }
            if (calDay.isWeekend) {
                return 'bg-gray-100 dark:bg-gray-700'
            }
            return 'bg-gray-50 dark:bg-gray-800'
        },

        getDayCellClass(calDay) {
            let classes =
                'px-1 py-1 border-b border-r dark:border-gray-700 text-center cursor-pointer hover:opacity-80 transition-opacity '

            if (calDay.isToday) {
                classes +=
                    'bg-yellow-50 dark:bg-yellow-900 border-l-2 border-r-2 border-yellow-400 dark:border-yellow-600'
            } else if (calDay.isWeekend) {
                classes += 'bg-gray-100 dark:bg-gray-800'
            } else {
                classes += 'bg-white dark:bg-gray-900'
            }

            return classes
        },

        isHoliday(day) {
            return day && day.is_holiday
        },

        isUnexcused(day) {
            if (! day) return false

            return (
                day.is_work_day &&
                ! day.absence_requests?.length &&
                day.actual_hours == 0
            )
        },
    }"
>
    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <h1
                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                >
                    {{ __('Attendance Overview') }}
                </h1>

                <div class="flex items-center gap-2">
                    <x-button
                        wire:click="previousMonth"
                        icon="chevron-left"
                        color="secondary"
                        flat
                        circle
                    />

                    <div
                        class="min-w-[150px] text-center text-lg font-medium text-gray-900 dark:text-gray-100"
                    >
                        <span x-text="$wire.monthName"></span>
                        <span x-text="$wire.year"></span>
                    </div>

                    <x-button
                        wire:click="nextMonth"
                        icon="chevron-right"
                        color="secondary"
                        flat
                        circle
                    />
                </div>
            </div>
        </x-slot>

        <div>
            <x-spinner wire:loading />
            <x-table>
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th
                            class="min-w-[200px] border-b border-r bg-gray-50 px-4 py-2 text-left font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                        >
                            {{ __('Employee') }}
                        </th>
                        <th
                            class="min-w-[80px] border-b border-r bg-blue-50 px-2 py-2 text-center text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-blue-900 dark:text-gray-300"
                        >
                            <div>{{ __('Days') }}</div>
                            <div class="text-gray-500 dark:text-gray-400">
                                {{ __('Month') }}
                            </div>
                        </th>
                        <th
                            class="min-w-[90px] border-b border-r bg-purple-50 px-2 py-2 text-center text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-purple-900 dark:text-gray-300"
                        >
                            <div>{{ __('Hours') }}</div>
                            <div class="text-gray-500 dark:text-gray-400">
                                {{ __('Actual/Target') }}
                            </div>
                        </th>
                        <template
                            x-for="calDay in $wire.calendarDays"
                            :key="calDay.date"
                        >
                            <th
                                class="border-b border-r px-1 py-2 text-center text-xs font-medium dark:border-gray-700"
                                x-bind:class="getDayClass(calDay)"
                            >
                                <div
                                    class="text-gray-700 dark:text-gray-300"
                                    x-bind:class="calDay.isToday ? 'font-bold' : ''"
                                >
                                    <span x-text="calDay.day"></span>
                                </div>
                                <div
                                    class="text-gray-500 dark:text-gray-400"
                                    x-bind:class="calDay.isToday ? 'font-bold' : ''"
                                >
                                    <span x-text="calDay.weekDay"></span>
                                </div>
                                <template x-if="calDay.isToday">
                                    <div
                                        class="text-xs font-bold text-yellow-600 dark:text-yellow-400"
                                    >
                                        {{ __('Today') }}
                                    </div>
                                </template>
                            </th>
                        </template>
                    </tr>
                </thead>

                <template
                    x-for="[departmentKey, employees] in Object.entries($wire.attendanceData)"
                    :key="departmentKey"
                >
                    <tbody>
                        <tr
                            class="cursor-pointer bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
                            x-on:click="toggleDepartment(departmentKey)"
                        >
                            <td
                                x-bind:colspan="Object.keys($wire.calendarDays).length + 3"
                                class="border-b px-4 py-2 font-semibold dark:border-gray-700"
                            >
                                <div class="flex items-center gap-2">
                                    <x-icon
                                        name="chevron-right"
                                        class="h-4 w-4 text-gray-600 transition-transform dark:text-gray-400"
                                        x-bind:class="{ 'rotate-90': isDepartmentExpanded(departmentKey) }"
                                    />
                                    <span
                                        x-text="departments[departmentKey]?.name || '{{ __('Unknown Department') }}'"
                                    ></span>
                                    <x-badge
                                        :text="''"
                                        x-text="Object.keys(employees).length + ' ' + '{{ __('Employees') }}'"
                                        color="gray"
                                        size="sm"
                                    />
                                </div>
                            </td>
                        </tr>

                        <template
                            x-for="employee in employees"
                            :key="employee.id"
                        >
                            <tr
                                class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                x-show="isDepartmentExpanded(departmentKey)"
                                x-cloak
                                x-transition
                            >
                                <td
                                    class="border-b border-r bg-white px-4 py-2 pl-8 dark:border-gray-700 dark:bg-gray-900"
                                >
                                    <x-link
                                        href="#"
                                        x-on:click.prevent="$wire.showEmployee(employee.id)"
                                    >
                                        <span x-text="employee.name"></span>
                                    </x-link>
                                </td>

                                <td
                                    class="cursor-pointer border-b border-r bg-blue-50 px-2 py-2 text-center transition-opacity hover:opacity-80 dark:border-gray-700 dark:bg-blue-900"
                                    x-on:click="$wire.showWorkTime(employee.id)"
                                >
                                    <div class="text-sm font-medium">
                                        <span
                                            x-text="employee.actual_days"
                                        ></span>
                                        /
                                        <span
                                            x-text="employee.target_days"
                                        ></span>
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        <span
                                            x-text="employee.days_percentage"
                                        ></span>
                                        %
                                    </div>
                                </td>

                                <td
                                    class="cursor-pointer border-b border-r bg-purple-50 px-2 py-2 text-center transition-opacity hover:opacity-80 dark:border-gray-700 dark:bg-purple-900"
                                    x-on:click="$wire.showWorkTime(employee.id)"
                                >
                                    <div class="text-sm font-medium">
                                        <span
                                            x-text="window.formatters.float(employee.actual_hours)"
                                        ></span>
                                        /
                                        <span
                                            x-text="window.formatters.float(employee.target_hours)"
                                        ></span>
                                        h
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        <span
                                            x-text="window.formatters.float(employee.hours_percentage)"
                                        ></span>
                                        %
                                    </div>
                                </td>

                                <template
                                    x-for="calDay in Object.values($wire.calendarDays)"
                                    :key="calDay.date"
                                >
                                    <td
                                        x-bind:class="getDayCellClass(calDay)"
                                        class="text-xs"
                                    >
                                        <div
                                            class="flex flex-col items-center justify-center gap-1"
                                        >
                                            <div
                                                x-cloak
                                                x-show="isHoliday(employee.days[calDay.date])"
                                                class="flex size-10 items-center justify-center rounded font-semibold text-white"
                                                x-bind:style="'background-color: ' + absenceTypes['holiday']?.color"
                                                x-bind:class="{ 'opacity-0': isStatusHidden('holiday') }"
                                            >
                                                <span
                                                    x-text="absenceTypes['holiday']?.icon"
                                                ></span>
                                            </div>

                                            <div
                                                x-on:click="$wire.showEmployeeDay(employee.days[calDay.date].id)"
                                                x-cloak
                                                x-show="employee.days[calDay.date]?.actual_hours > 0"
                                                class="flex size-10 items-center justify-center rounded border-green-500 bg-green-500 text-green-50 dark:border-transparent dark:bg-green-700 dark:bg-opacity-80"
                                                x-bind:style="'background-color: ' + absenceTypes['present']?.color"
                                                x-bind:class="{
                                                    'opacity-0': isStatusHidden('present'),
                                                    'ring-2 ring-offset-1 ring-green-400 dark:ring-green-600':
                                                        employee.days[calDay.date]?.plus_minus_overtime_hours > 0,
                                                    'ring-2 ring-offset-1 ring-red-400 dark:ring-red-600':
                                                        employee.days[calDay.date]?.plus_minus_overtime_hours < 0,
                                                }"
                                            >
                                                <span
                                                    x-text="window.formatters.float(employee.days[calDay.date]?.actual_hours) + 'h'"
                                                ></span>
                                            </div>

                                            <div
                                                x-on:click="$wire.showWorkTime(employee.id, calDay.date)"
                                                x-cloak
                                                x-show="employee.days[calDay.date]?.is_daily_work_time || false"
                                                class="flex size-10 items-center justify-center rounded font-semibold text-white"
                                                x-bind:style="'background-color: ' + absenceTypes['working']?.color"
                                                x-bind:class="{
                                                    'opacity-0': isStatusHidden('working'),
                                                    'animate-pulse': ! isStatusHidden('working'),
                                                }"
                                            >
                                                <span
                                                    x-text="absenceTypes['working']?.icon"
                                                ></span>
                                            </div>

                                            <template
                                                x-for="absence in employee.days[calDay.date]?.absence_requests ?? []"
                                                :key="absence.id"
                                            >
                                                <div
                                                    x-on:click="$wire.showAbsenceRequest(absence.id)"
                                                    class="flex size-10 items-center justify-center rounded font-semibold text-white"
                                                    x-bind:style="'background-color: ' + absenceTypes[absence.absence_type_id]?.color"
                                                    x-bind:class="{ 'opacity-0': isStatusHidden(absence.absence_type_id) }"
                                                >
                                                    <span
                                                        x-text="absenceTypes[absence.absence_type_id]?.icon"
                                                    ></span>
                                                </div>
                                            </template>

                                            <div
                                                x-on:click="
                                                    employee.days[calDay.date].id
                                                        ? $wire.showEmployeeDay(employee.days[calDay.date].id)
                                                        : null
                                                "
                                                x-cloak
                                                x-show="isUnexcused(employee.days[calDay.date])"
                                                class="flex size-10 items-center justify-center rounded bg-red-500 text-red-50 dark:border-transparent dark:bg-red-700 dark:bg-opacity-80"
                                                x-bind:style="'background-color: ' + absenceTypes['absent']?.color"
                                                x-bind:class="{ 'opacity-0': isStatusHidden('absent') }"
                                            >
                                                <span
                                                    x-text="
                                                        window.formatters.float(employee.days[calDay.date]?.plus_minus_overtime_hours) +
                                                            'h'
                                                    "
                                                ></span>
                                            </div>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </template>
            </x-table>
        </div>

        <x-slot:footer>
            <div class="flex flex-wrap items-center gap-4">
                <template x-for="type in absenceTypes" :key="type.id">
                    <x-button
                        x-on:click="toggleStatus(type.id)"
                        color="secondary"
                        flat
                        size="xs"
                        x-bind:class="{ 'opacity-30': isStatusHidden(type.id) }"
                    >
                        <div
                            class="mr-2 size-6 rounded"
                            x-bind:style="'background-color: ' + type.color"
                        ></div>
                        <span
                            class="whitespace-nowrap text-gray-600 dark:text-gray-400"
                            x-text="type.name"
                        ></span>
                    </x-button>
                </template>
            </div>
        </x-slot>
    </x-card>
</div>
