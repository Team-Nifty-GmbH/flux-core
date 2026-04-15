<div x-data="{ expandedDepts: {} }" class="flex h-full flex-col">
    <div class="mb-3 flex items-center justify-between">
        <x-button
            wire:click="previousMonth()"
            icon="chevron-left"
            color="secondary"
            flat
            circle
            sm
        />

        <div
            class="text-center text-sm font-semibold text-gray-900 dark:text-gray-100"
        >
            {{ $monthName }} {{ $year }}
        </div>

        <x-button
            wire:click="nextMonth()"
            icon="chevron-right"
            color="secondary"
            flat
            circle
            sm
        />
    </div>

    <div class="flex-1 overflow-auto">
        <x-loading wire:loading />
        <table
            class="dark:divide-dark-500/50 min-w-full divide-y divide-gray-200"
        >
            <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th
                        class="sticky left-0 z-20 min-w-[120px] border-r border-b bg-gray-50 px-2 py-1 text-left text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    >
                        {{ __('Employee') }}
                    </th>
                    @foreach($calendarDays as $calDay)
                        <th
                            @class([
                                'border-r border-b px-0.5 py-1 text-center text-xs font-medium dark:border-gray-700',
                                'bg-yellow-100 dark:bg-yellow-900 border-l-2 border-r-2 border-t-2 border-yellow-400 dark:border-yellow-600' => $calDay['isToday'],
                                'bg-gray-100 dark:bg-gray-700' => $calDay['isWeekend'] && ! $calDay['isToday'],
                                'bg-gray-50 dark:bg-gray-800' => ! $calDay['isWeekend'] && ! $calDay['isToday'],
                            ])
                        >
                            <div class="text-gray-700 dark:text-gray-300">
                                {{ $calDay['day'] }}
                            </div>
                            <div class="text-gray-500 dark:text-gray-400">
                                {{ $calDay['weekDay'] }}
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach($departments as $deptIndex => $department)
                    <tr
                        class="cursor-pointer bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
                        x-on:click="expandedDepts[{{ $deptIndex }}] = !(expandedDepts[{{ $deptIndex }}] ?? true)"
                    >
                        <td
                            colspan="{{ count($calendarDays) + 1 }}"
                            class="border-b px-2 py-1.5 font-semibold dark:border-gray-700"
                        >
                            <div class="flex items-center gap-2">
                                <x-icon
                                    name="chevron-right"
                                    class="h-4 w-4 text-gray-600 transition-transform dark:text-gray-400"
                                    x-bind:class="{ 'rotate-90': expandedDepts[{{ $deptIndex }}] ?? true }"
                                />
                                <span class="text-xs">
                                    {{ $department['name'] }}
                                </span>
                                <x-badge
                                    :text="count($department['employees']) . ' ' . __('Employees')"
                                    color="gray"
                                    size="sm"
                                />
                            </div>
                        </td>
                    </tr>
                    @foreach($department['employees'] as $employee)
                        <tr
                            x-cloak
                            x-show="expandedDepts[{{ $deptIndex }}] ?? true"
                            x-transition
                            class="hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            <td
                                class="sticky left-0 z-[5] border-r border-b bg-white px-2 py-1 pl-8 dark:border-gray-700 dark:bg-gray-900"
                            >
                                <span
                                    class="truncate text-xs text-gray-800 dark:text-gray-200"
                                >
                                    {{ $employee['name'] }}
                                </span>
                            </td>

                            @foreach($calendarDays as $dateKey => $calDay)
                                @php
                                    $dayData = $employee['days'][$dateKey] ?? null;
                                @endphp
                                <td
                                    @class([
                                        'border-r border-b px-0.5 py-0.5 text-center dark:border-gray-700',
                                        'border-l-2 border-r-2 border-yellow-400 bg-yellow-50 dark:border-yellow-600 dark:bg-yellow-900/30' => $calDay['isToday'],
                                        'bg-gray-100 dark:bg-gray-700' => $calDay['isWeekend'] && ! $calDay['isToday'],
                                        'bg-white dark:bg-gray-900' => ! $calDay['isWeekend'] && ! $calDay['isToday'],
                                    ])
                                    @if($dayData)
                                        title="{{ $dayData['name'] }}"
                                    @endif
                                >
                                    @if($dayData)
                                        <div
                                            @class([
                                                'mx-auto size-5 rounded',
                                                'opacity-70' => $dayData['is_half_day'],
                                            ])
                                            style="background-color: {{ $dayData['color'] }}"
                                        ></div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <div
        class="mt-2 flex flex-wrap items-center gap-3 border-t pt-2 dark:border-gray-700"
    >
        @foreach($absenceTypes as $type)
            <div class="flex items-center gap-1.5">
                <div
                    class="size-3 shrink-0 rounded"
                    style="background-color: {{ $type['color'] }}"
                ></div>
                <span class="text-xs text-gray-600 dark:text-gray-400">
                    {{ $type['name'] }}
                </span>
            </div>
        @endforeach
    </div>
</div>
