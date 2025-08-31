<div class="flex flex-col h-full"
    x-data="{
        expandedDepartments: {},
        hiddenStatuses: {},
        absenceTypes: @js($absenceTypes),
        departments: @js($departments),

        toggleDepartment(deptId) {
            if (this.expandedDepartments[deptId] === undefined) {
                this.expandedDepartments[deptId] = true;
            }
            this.expandedDepartments[deptId] = !this.expandedDepartments[deptId];
        },

        isDepartmentExpanded(deptId) {
            return this.expandedDepartments[deptId] !== false;
        },

        toggleStatus(status) {
            this.hiddenStatuses[status] = !this.hiddenStatuses[status];
        },

        isStatusHidden(status) {
            return this.hiddenStatuses[status] === true;
        },

        getDayClass(calDay) {
            if (calDay.isToday) {
                return 'bg-yellow-100 dark:bg-yellow-900 border-l-2 border-r-2 border-t-2 border-yellow-400 dark:border-yellow-600';
            }
            if (calDay.isWeekend) {
                return 'bg-gray-100 dark:bg-gray-700';
            }
            return 'bg-gray-50 dark:bg-gray-800';
        },

        getDayCellClass(calDay) {
            let classes = 'px-1 py-1 border-b border-r dark:border-gray-700 text-center cursor-pointer hover:opacity-80 transition-opacity ';

            if (calDay.isToday) {
                classes += 'bg-yellow-50 dark:bg-yellow-900 border-l-2 border-r-2 border-yellow-400 dark:border-yellow-600';
            } else if (calDay.isWeekend) {
                classes += 'bg-gray-100 dark:bg-gray-800';
            } else {
                classes += 'bg-white dark:bg-gray-900';
            }

            return classes;
        },

        getEmployeeDay(employee, dateString) {
            if (!employee || !employee.days) return null;
            return employee.days[dateString] || null;
        },

        isHoliday(locationId, dateString) {
            const holidays = $wire.holidays[locationId || 'no-location'] || {};
            return holidays[dateString] !== undefined;
        },

        isUnexcused(day) {
            if (!day) return false;

            return (day.is_work_day && ! day.absence_requests?.length && day.actual_hours == 0);
        },

        getHolidayName(locationId, dateString) {
            const holidays = $wire.holidays[locationId || 'no-location'] || {};
            return holidays[dateString]?.name || '';
        },

        getAbsenceType(absenceTypeId) {
            return this.absenceTypes[absenceTypeId] || null;
        }
    }"
>
    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Attendance Overview') }}</h1>

                <div class="flex items-center gap-2">
                    <x-button
                        wire:click="previousMonth"
                        icon="chevron-left"
                        color="secondary"
                        flat
                        circle
                    />

                    <div class="text-lg font-medium min-w-[150px] text-center text-gray-900 dark:text-gray-100">
                        <span x-text="$wire.monthName"></span> <span x-text="$wire.year"></span>
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
        </x-slot:header>

        <div>
            <x-spinner wire:loading />
            <x-table>
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="bg-gray-50 dark:bg-gray-800 px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700 border-r dark:border-gray-700 min-w-[200px]">
                            {{ __('Employee') }}
                        </th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700 border-r dark:border-gray-700 bg-blue-50 dark:bg-blue-900 min-w-[80px]">
                            <div>{{ __('Days') }}</div>
                            <div class="text-gray-500 dark:text-gray-400">{{ __('Month') }}</div>
                        </th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700 border-r dark:border-gray-700 bg-purple-50 dark:bg-purple-900 min-w-[90px]">
                            <div>{{ __('Hours') }}</div>
                            <div class="text-gray-500 dark:text-gray-400">{{ __('Actual/Target') }}</div>
                        </th>
                        <template x-for="calDay in $wire.calendarDays" :key="calDay.date">
                            <th class="px-1 py-2 text-center text-xs font-medium border-b border-r dark:border-gray-700"
                                x-bind:class="getDayClass(calDay)">
                                <div class="text-gray-700 dark:text-gray-300" x-bind:class="calDay.isToday ? 'font-bold' : ''">
                                    <span x-text="calDay.day"></span>
                                </div>
                                <div class="text-gray-500 dark:text-gray-400" x-bind:class="calDay.isToday ? 'font-bold' : ''">
                                    <span x-text="calDay.weekDay"></span>
                                </div>
                                <template x-if="calDay.isToday">
                                    <div class="text-xs text-yellow-600 dark:text-yellow-400 font-bold">{{ __('Today') }}</div>
                                </template>
                            </th>
                        </template>
                    </tr>
                </thead>

                    <template x-for="[departmentKey, employees] in Object.entries($wire.attendanceData)" :key="departmentKey">
                        <tbody>
                            <tr class="bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer"
                                x-on:click="toggleDepartment(departmentKey)">
                                <td x-bind:colspan="Object.keys($wire.calendarDays).length + 3" class="px-4 py-2 font-semibold border-b dark:border-gray-700">
                                    <div class="flex items-center gap-2">
                                        <x-icon
                                            name="chevron-right"
                                            class="w-4 h-4 transition-transform text-gray-600 dark:text-gray-400"
                                            x-bind:class="{ 'rotate-90': isDepartmentExpanded(departmentKey) }"
                                        />
                                        <span x-text="departments[departmentKey]?.name || '{{ __('Unknown Department') }}'"></span>
                                        <x-badge
                                            :text="''"
                                            x-text="Object.keys(employees).length + ' ' + '{{ __('Employees') }}'"
                                            color="gray"
                                            size="sm"
                                        />
                                    </div>
                                </td>
                            </tr>

                            <template x-for="employee in employees" :key="employee.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                    x-show="isDepartmentExpanded(departmentKey)"
                                    x-cloak
                                    x-transition
                                >
                                    <td class="bg-white dark:bg-gray-900 px-4 py-2 border-b border-r dark:border-gray-700 pl-8">
                                        <x-link
                                            href="#"
                                            x-on:click.prevent="$wire.showEmployee(employee.id)"
                                        >
                                            <span x-text="employee.name"></span>
                                        </x-link>
                                    </td>

                                    <td class="px-2 py-2 border-b dark:border-gray-700 border-r text-center bg-blue-50 dark:bg-blue-900 cursor-pointer hover:opacity-80 transition-opacity"
                                        x-on:click="$wire.showWorkTime(employee.id)">
                                        <div class="text-sm font-medium">
                                            <span x-text="employee.actual_days"></span> / <span x-text="employee.target_days"></span>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <span x-text="employee.days_percentage"></span>%
                                        </div>
                                    </td>

                                    <td class="px-2 py-2 border-b dark:border-gray-700 border-r text-center bg-purple-50 dark:bg-purple-900 cursor-pointer hover:opacity-80 transition-opacity"
                                        x-on:click="$wire.showWorkTime(employee.id)">
                                        <div class="text-sm font-medium">
                                            <span x-text="window.formatters.float(employee.actual_hours)"></span> / <span x-text="window.formatters.float(employee.target_hours)"></span>h
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <span x-text="window.formatters.float(employee.hours_percentage)"></span>%
                                        </div>
                                    </td>

                                    <template x-for="calDay in Object.values($wire.calendarDays)" :key="calDay.date">
                                        <td x-bind:class="getDayCellClass(calDay)" class="text-xs">
                                            <div class="flex flex-col gap-1 items-center justify-center">
                                               <div
                                                   x-cloak
                                                   x-show="isHoliday(employee.location_id, calDay.date)"
                                                   class="size-10 rounded flex items-center justify-center text-white font-semibold"
                                                   x-bind:style="'background-color: ' + absenceTypes['holiday']?.color"
                                                   x-bind:class="{ 'opacity-0': isStatusHidden('holiday') }"
                                               >
                                                    <span x-text="absenceTypes['holiday']?.icon"></span>
                                                </div>

                                               <div
                                                   x-on:click="$wire.showEmployeeDay(employee.days[calDay.date].id)"
                                                   x-cloak
                                                   x-show="employee.days[calDay.date]?.actual_hours > 0"
                                                   class="size-10 rounded flex items-center justify-center border-green-500 bg-green-500 dark:bg-green-700 dark:bg-opacity-80 dark:border-transparent text-green-50"
                                                   x-bind:style="'background-color: ' + absenceTypes['present']?.color"
                                                   x-bind:class="{
                                                        'opacity-0': isStatusHidden('present'),
                                                        'ring-2 ring-offset-1 ring-green-400 dark:ring-green-600': employee.days[calDay.date]?.plus_minus_overtime_hours > 0,
                                                        'ring-2 ring-offset-1 ring-red-400 dark:ring-red-600': employee.days[calDay.date]?.plus_minus_overtime_hours < 0
                                                    }"
                                               >
                                                    <span x-text="window.formatters.float(employee.days[calDay.date]?.actual_hours) + 'h'"></span>
                                                </div>

                                               <div
                                                   x-on:click="$wire.showWorkTime(employee.id, calDay.date)"
                                                   x-cloak
                                                   x-show="employee.days[calDay.date]?.is_daily_work_time || false"
                                                   class="size-10 rounded flex items-center justify-center text-white font-semibold"
                                                   x-bind:style="'background-color: ' + absenceTypes['working']?.color"
                                                   x-bind:class="{
                                                        'opacity-0': isStatusHidden('working'),
                                                        'animate-pulse': ! isStatusHidden('working')
                                                 }"
                                               >
                                                    <span x-text="absenceTypes['working']?.icon"></span>
                                                </div>

                                                <template x-for="absence in employee.days[calDay.date]?.absence_requests ?? []" :key="absence.id">
                                                   <div
                                                       x-on:click="$wire.showAbsenceRequest(absence.id)"
                                                       class="size-10 rounded flex items-center justify-center text-white font-semibold"
                                                       x-bind:style="'background-color: ' + absenceTypes[absence.absence_type_id]?.color"
                                                       x-bind:class="{ 'opacity-0': isStatusHidden(absence.absence_type_id) }"
                                                   >
                                                        <span x-text="absenceTypes[absence.absence_type_id]?.icon"></span>
                                                    </div>
                                                </template>

                                               <div
                                                   x-on:click="$wire.showEmployeeDay(employee.days[calDay.date].id)"
                                                   x-cloak
                                                   x-show="isUnexcused(employee.days[calDay.date])"
                                                   class="size-10 rounded flex items-center justify-center bg-red-500 dark:bg-red-700 dark:bg-opacity-80 dark:border-transparent text-red-50"
                                                   x-bind:style="'background-color: ' + absenceTypes['absent']?.color"
                                                   x-bind:class="{ 'opacity-0': isStatusHidden('absent') }"
                                               >
                                                    <span x-text="window.formatters.float(employee.days[calDay.date]?.plus_minus_overtime_hours) + 'h'"></span>
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
            <div class="flex items-center gap-4 flex-wrap">
                <template x-for="type in absenceTypes" :key="type.id">
                    <x-button
                        x-on:click="toggleStatus(type.id)"
                        color="secondary"
                        flat
                        size="xs"
                        x-bind:class="{ 'opacity-30': isStatusHidden(type.id) }">
                        <div class="size-6 rounded mr-2" x-bind:style="'background-color: ' + type.color"></div>
                        <span class="text-gray-600 dark:text-gray-400 whitespace-nowrap" x-text="type.name"></span>
                    </x-button>
                </template>

                <x-button
                    color="secondary"
                    flat
                    size="xs"
                    x-bind:class="{ 'opacity-30': isStatusHidden(type.id) }">
                    <div class="size-6 rounded mr-2 bg-gray-100"></div>
                    <span class="text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ __('Weekend') }}</span>
                </x-button>
            </div>
        </x-slot:footer>
    </x-card>
</div>
