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
            $dispatch('department-toggled', {
                deptId: deptId,
                expanded: this.expandedDepartments[deptId],
            })
        },

        isDepartmentExpanded(deptId) {
            return this.expandedDepartments[deptId] !== false
        },

        toggleStatus(status) {
            this.hiddenStatuses[status] = ! this.hiddenStatuses[status]
            $dispatch('status-toggled', {
                status: status,
                hidden: this.hiddenStatuses[status],
            })
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
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-dark-500/50"
            >
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
                        @foreach ($this->calendarDays as $calDay)
                            <th
                                class="{{ $calDay['isToday'] ? 'border-l-2 border-r-2 border-t-2 border-yellow-400 bg-yellow-100 dark:border-yellow-600 dark:bg-yellow-900' : ($calDay['isWeekend'] ? 'bg-gray-100 dark:bg-gray-700' : 'bg-gray-50 dark:bg-gray-800') }} border-b border-r px-1 py-2 text-center text-xs font-medium dark:border-gray-700"
                            >
                                <div
                                    class="{{ $calDay['isToday'] ? 'font-bold' : '' }} text-gray-700 dark:text-gray-300"
                                >
                                    {{ $calDay['day'] }}
                                </div>
                                <div
                                    class="{{ $calDay['isToday'] ? 'font-bold' : '' }} text-gray-500 dark:text-gray-400"
                                >
                                    {{ $calDay['weekDay'] }}
                                </div>
                                @if ($calDay['isToday'])
                                    <div
                                        class="text-xs font-bold text-yellow-600 dark:text-yellow-400"
                                    >
                                        {{ __('Today') }}
                                    </div>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>

                @foreach ($this->employeesByDepartment as $departmentId => $employees)
                    <tbody wire:key="dept-{{ $departmentId }}">
                        <tr
                            class="cursor-pointer bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
                            x-on:click="toggleDepartment('{{ $departmentId }}')"
                        >
                            <td
                                colspan="{{ count($this->calendarDays) + 3 }}"
                                class="border-b px-4 py-2 font-semibold dark:border-gray-700"
                            >
                                <div class="flex items-center gap-2">
                                    <x-icon
                                        name="chevron-right"
                                        class="h-4 w-4 text-gray-600 transition-transform dark:text-gray-400"
                                        x-bind:class="{ 'rotate-90': isDepartmentExpanded('{{ $departmentId }}') }"
                                    />
                                    <span>
                                        {{ $departments[$departmentId]['name'] ?? __('Unknown Department') }}
                                    </span>
                                    <x-badge
                                        :text="count($employees) . ' ' . __('Employees')"
                                        color="gray"
                                        size="sm"
                                    />
                                </div>
                            </td>
                        </tr>

                        @foreach ($employees as $employee)
                            <livewire:human-resources.attendance-overview-row
                                :key="'row-' . $employee['id'] . '-' . $this->year . '-' . $this->month"
                                :employeeId="$employee['id']"
                                :year="$this->year"
                                :month="$this->month"
                                :calendarDays="$this->calendarDays"
                                :departmentId="$departmentId"
                                :absenceTypes="$absenceTypes"
                                lazy
                            />
                        @endforeach
                    </tbody>
                @endforeach
            </table>
        </div>

        <x-slot:footer>
            <div class="flex flex-wrap items-center gap-4">
                @foreach ($absenceTypes as $type)
                    <x-button
                        x-on:click="toggleStatus('{{ $type['id'] }}')"
                        color="secondary"
                        flat
                        size="xs"
                        x-bind:class="{ 'opacity-30': isStatusHidden('{{ $type['id'] }}') }"
                    >
                        <div
                            class="mr-2 size-6 shrink-0 rounded"
                            style="background-color: {{ $type['color'] }}"
                        ></div>
                        <span
                            class="max-w-[150px] truncate text-gray-600 dark:text-gray-400"
                        >
                            {{ $type['name'] }}
                        </span>
                    </x-button>
                @endforeach
            </div>
        </x-slot>
    </x-card>
</div>
