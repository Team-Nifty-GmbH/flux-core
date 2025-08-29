<div class="flex flex-col h-full"
    x-data="{
        expandedDepartments: {},
        hiddenStatuses: {},

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
            if (calDay.isHoliday) {
                return 'bg-red-50 dark:bg-red-950';
            }
            if (calDay.isWeekend) {
                return 'bg-gray-100 dark:bg-gray-700';
            }
            return 'bg-gray-50 dark:bg-gray-800';
        },

        getDayCellClass(calDay, display) {
            let classes = 'px-1 py-1 border-b border-r dark:border-gray-700 text-center ';

            if (display && display.isClickable) {
                classes += 'cursor-pointer hover:opacity-80 transition-opacity ';
            }

            if (calDay.isToday) {
                classes += 'bg-yellow-50 dark:bg-yellow-900 border-l-2 border-r-2 border-yellow-400 dark:border-yellow-600';
            } else if (calDay.isWeekend) {
                classes += 'bg-gray-100 dark:bg-gray-800';
            } else {
                classes += 'bg-white dark:bg-gray-900';
            }

            return classes;
        }
    }">
    <div class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Attendance Overview') }}</h1>

            <div class="flex items-center gap-4">
                <button
                    wire:click="previousMonth"
                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition text-gray-600 dark:text-gray-400"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="text-lg font-medium min-w-[150px] text-center text-gray-900 dark:text-gray-100">
                    <span x-text="$wire.monthName"></span> <span x-text="$wire.year"></span>
                </div>

                <button
                    wire:click="nextMonth"
                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition text-gray-600 dark:text-gray-400"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="flex-1 overflow-auto bg-gray-50 dark:bg-gray-900">
        <table class="w-full border-collapse">
            {{-- Table Header --}}
            <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800 z-10">
                <tr>
                    <th class="sticky left-0 z-20 bg-gray-50 dark:bg-gray-800 px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700 border-r dark:border-gray-700 min-w-[200px]">
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
                            x-bind:class="getDayClass(calDay)"
                            x-bind:title="calDay.holidayName || ''">
                            <div class="text-gray-700 dark:text-gray-300" x-bind:class="calDay.isToday ? 'font-bold' : ''">
                                <span x-text="calDay.day"></span>
                            </div>
                            <div class="text-gray-500 dark:text-gray-400" x-bind:class="calDay.isToday ? 'font-bold' : (calDay.isHoliday ? 'text-red-600 dark:text-red-400' : '')">
                                <span x-text="calDay.weekDay"></span>
                            </div>
                            <template x-if="calDay.isToday">
                                <div class="text-xs text-yellow-600 dark:text-yellow-400 font-bold">{{ __('Today') }}</div>
                            </template>
                            <template x-if="calDay.isHoliday && !calDay.isToday">
                                <div class="text-xs text-red-600 dark:text-red-400 font-semibold">{{ __('Holiday') }}</div>
                            </template>
                        </th>
                    </template>
                </tr>
            </thead>

            {{-- Table Body --}}
            <template x-for="[departmentKey, department] in Object.entries($wire.departments)" :key="departmentKey">
                <tbody>
                    {{-- Department Header Row --}}
                    <tr class="bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer"
                        x-on:click="toggleDepartment(departmentKey)">
                        <td x-bind:colspan="$wire.calendarDays.length + 3" class="px-4 py-2 font-semibold border-b dark:border-gray-700 text-gray-900 dark:text-gray-100">
                            <div class="flex items-center gap-2">
                                <svg
                                    class="w-4 h-4 transition-transform text-gray-600 dark:text-gray-400"
                                    x-bind:class="{ 'rotate-90': isDepartmentExpanded(departmentKey) }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <span x-text="department.name"></span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    (<span x-text="department.userCount"></span> {{ __('Employees') }})
                                </span>
                            </div>
                        </td>
                    </tr>

                    {{-- Department Users --}}
                    <template x-for="[userId, user] in Object.entries($wire.attendanceData[departmentKey] || {})" :key="userId">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800"
                            x-show="isDepartmentExpanded(departmentKey)"
                            x-cloak
                            x-transition>
                            {{-- User Name --}}
                            <td class="sticky left-0 bg-white dark:bg-gray-900 px-4 py-2 border-b dark:border-gray-700 border-r dark:border-gray-700 font-medium pl-8 text-gray-900 dark:text-gray-100">
                                <a x-bind:href="user.url" 
                                   wire:navigate
                                   class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    <span x-text="user.name"></span>
                                </a>
                            </td>

                            {{-- Monthly Total Days --}}
                            <td class="px-2 py-2 border-b dark:border-gray-700 border-r dark:border-gray-700 text-center bg-blue-50 dark:bg-blue-900 cursor-pointer hover:opacity-80 transition-opacity"
                                x-on:click="$wire.showDetail(userId, $wire.year + '-' + String($wire.month).padStart(2, '0'))"
                                title="{{ __('Click for monthly details') }}">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <span x-text="user.actual_days"></span> / <span x-text="user.work_days"></span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <span x-text="user.attendance_percentage"></span>%
                                </div>
                            </td>

                            {{-- Monthly Total Hours --}}
                            <td class="px-2 py-2 border-b dark:border-gray-700 border-r dark:border-gray-700 text-center bg-purple-50 dark:bg-purple-900 cursor-pointer hover:opacity-80 transition-opacity"
                                x-on:click="$wire.showDetail(userId, $wire.year + '-' + String($wire.month).padStart(2, '0'))"
                                title="{{ __('Click for monthly hours details') }}">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <span x-text="user.actual_hours_formatted"></span> / <span x-text="user.target_hours_formatted"></span>h
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <span x-text="user.hours_percentage"></span>%
                                </div>
                            </td>

                            {{-- Daily Status Cells --}}
                            <template x-for="(calDay, index) in $wire.calendarDays" :key="calDay.date">
                                <td x-bind:class="getDayCellClass(calDay, user.days[index + 1]?.display)"
                                    x-on:click="user.days[index + 1]?.display?.isClickable && $wire.showDetail(userId, user.days[index + 1].date)"
                                    x-bind:title="user.days[index + 1]?.display?.title || (calDay.holidayName || '')">

                                    {{-- Holiday indicator --}}
                                    <template x-if="user.days[index + 1]?.display?.type === 'holiday'">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-8 h-8 rounded bg-red-100 dark:bg-red-900 border border-red-300 dark:border-red-700 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z"/>
                                                </svg>
                                            </div>
                                            <div class="text-xs mt-1 invisible">0h</div>
                                        </div>
                                    </template>

                                    <template x-if="user.days[index + 1]?.display?.isPartial">
                                        {{-- Partial day (present + absence) --}}
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="relative w-8 h-8"
                                                 x-bind:class="{
                                                     'opacity-80 animate-pulse': user.days[index + 1]?.display?.isRunning
                                                 }">
                                                <div class="absolute inset-0 w-4 h-8 rounded-l bg-green-400 dark:bg-green-600"
                                                     x-bind:class="{ 'opacity-0': isStatusHidden('present') }"></div>
                                                <div class="absolute right-0 top-0 w-4 h-8 rounded-r"
                                                     x-bind:style="'background-color: ' + (user.days[index + 1]?.display?.partialColor || '')"
                                                     x-bind:class="{ 'opacity-0': isStatusHidden('absence_' + (user.days[index + 1]?.display?.absenceTypeId || '')) }"></div>
                                            </div>
                                            <template x-if="user.days[index + 1]?.display?.text">
                                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                    <span x-text="user.days[index + 1].display.text"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="!user.days[index + 1]?.display?.isPartial && user.days[index + 1]?.display?.type === 'present'">
                                        {{-- Full day present --}}
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-8 h-8 rounded bg-green-400 dark:bg-green-600 flex items-center justify-center"
                                                 x-bind:class="{
                                                     'opacity-0': isStatusHidden('present'),
                                                     'opacity-80 animate-pulse': user.days[index + 1]?.display?.isRunning
                                                 }">
                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <template x-if="user.days[index + 1]?.display?.text">
                                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                    <span x-text="user.days[index + 1].display.text"></span>
                                                </div>
                                            </template>
                                            <template x-if="!user.days[index + 1]?.display?.text">
                                                <div class="text-xs mt-1 invisible">0h</div>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="!user.days[index + 1]?.display?.isPartial && user.days[index + 1]?.display?.type === 'absence'">
                                        {{-- Approved absence --}}
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-8 h-8 rounded flex items-center justify-center text-white font-semibold"
                                                 x-bind:style="'background-color: ' + (user.days[index + 1]?.display?.color || '')"
                                                 x-bind:class="{ 'opacity-0': isStatusHidden('absence_' + (user.days[index + 1]?.display?.absenceTypeId || '')) }">
                                                <span x-text="user.days[index + 1]?.display?.text || ''"></span>
                                            </div>
                                            <div class="text-xs mt-1 invisible">0h</div>
                                        </div>
                                    </template>

                                    <template x-if="!user.days[index + 1]?.display?.isPartial && user.days[index + 1]?.display?.type === 'absent'">
                                        {{-- Unexcused absence --}}
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-8 h-8 rounded bg-red-400 dark:bg-red-600 flex items-center justify-center text-white font-bold"
                                                 x-bind:class="{ 'opacity-0': isStatusHidden('absent') }">
                                                <span x-text="user.days[index + 1]?.display?.text || ''"></span>
                                            </div>
                                            <div class="text-xs mt-1 invisible">0h</div>
                                        </div>
                                    </template>
                                </td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </template>

            <tfoot>
                <tr class="bg-white dark:bg-gray-800 border-t-2 dark:border-gray-600">
                    <td x-bind:colspan="$wire.calendarDays.length + 3" class="px-6 py-3">
                        <div class="flex items-center gap-4">
                            <span class="font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ __('Legend') }}:</span>
                            
                            <div class="flex-1 overflow-x-auto">
                                <div class="flex items-center gap-6 text-sm min-w-max">
                                    <button
                                        x-on:click="toggleStatus('present')"
                                        class="flex items-center gap-2 hover:opacity-75 transition-opacity"
                                        x-bind:class="{ 'opacity-30': isStatusHidden('present') }">
                                        <div class="w-4 h-4 rounded bg-green-400 dark:bg-green-600"></div>
                                        <span class="text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ __('Present') }}</span>
                                    </button>

                                    <div class="flex items-center gap-2">
                                        <div class="relative">
                                            <div class="absolute inset-0 w-4 h-4 rounded bg-green-400 dark:bg-green-600 animate-pulse opacity-50"></div>
                                            <div class="relative w-4 h-4 rounded bg-green-400 dark:bg-green-600"></div>
                                        </div>
                                        <span class="text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ __('Currently Working') }}</span>
                                    </div>

                                    <button
                                        x-on:click="toggleStatus('absent')"
                                        class="flex items-center gap-2 hover:opacity-75 transition-opacity"
                                        x-bind:class="{ 'opacity-30': isStatusHidden('absent') }">
                                        <div class="w-4 h-4 rounded bg-red-400 dark:bg-red-600"></div>
                                        <span class="text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ __('Unexcused') }}</span>
                                    </button>

                                    <template x-for="type in $wire.absenceTypes" :key="type.id">
                                        <button
                                            x-on:click="toggleStatus('absence_' + type.id)"
                                            class="flex items-center gap-2 hover:opacity-75 transition-opacity"
                                            x-bind:class="{ 'opacity-30': isStatusHidden('absence_' + type.id) }">
                                            <div class="w-4 h-4 rounded" x-bind:style="'background-color: ' + type.color"></div>
                                            <span class="text-gray-600 dark:text-gray-400 whitespace-nowrap" x-text="type.name"></span>
                                        </button>
                                    </template>

                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-gray-100 dark:bg-gray-700 rounded"></div>
                                        <span class="text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ __('Weekend') }}</span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-red-50 dark:bg-red-950 rounded border border-red-200 dark:border-red-800"></div>
                                        <span class="text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ __('Holiday') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
