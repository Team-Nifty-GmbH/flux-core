<tr
    x-cloak
    x-show="isDepartmentExpanded('{{ $departmentId }}')"
    x-transition
    class="hover:bg-gray-50 dark:hover:bg-gray-800"
>
    <td
        class="border-b border-r bg-white px-4 py-2 pl-8 dark:border-gray-700 dark:bg-gray-900"
    >
        <x-link href="#" x-on:click.prevent="$wire.showEmployee()">
            {{ data_get($employee, 'name', __('Unknown')) }}
        </x-link>
    </td>

    <td
        class="cursor-pointer border-b border-r bg-blue-50 px-2 py-2 text-center transition-opacity hover:opacity-80 dark:border-gray-700 dark:bg-blue-900"
        x-on:click="$wire.showWorkTime()"
    >
        <div class="text-sm font-medium">
            {{ data_get($employee, 'actual_days', 0) }} /
            {{ data_get($employee, 'target_days', 0) }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
            {{ number_format(data_get($employee, 'days_percentage', 0), 0) }}%
        </div>
    </td>

    <td
        class="cursor-pointer border-b border-r bg-purple-50 px-2 py-2 text-center transition-opacity hover:opacity-80 dark:border-gray-700 dark:bg-purple-900"
        x-on:click="$wire.showWorkTime()"
    >
        <div class="text-sm font-medium">
            {{ number_format(data_get($employee, 'actual_hours', 0), 1) }} /
            {{ number_format(data_get($employee, 'target_hours', 0), 1) }}h
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
            {{ number_format(data_get($employee, 'hours_percentage', 0), 0) }}%
        </div>
    </td>

    @foreach ($calendarDays as $dateKey => $calDay)
        @php
            $dayData = data_get($employee, 'days.' . $dateKey);
        @endphp

        <td
            class="{{ data_get($calDay, 'isToday') ? 'border-l-2 border-r-2 border-yellow-400 bg-yellow-50 dark:border-yellow-600 dark:bg-yellow-900' : (data_get($calDay, 'isWeekend') ? 'bg-gray-100 dark:bg-gray-800' : 'bg-white dark:bg-gray-900') }} cursor-pointer border-b border-r px-1 py-1 text-center text-xs transition-opacity hover:opacity-80 dark:border-gray-700"
        >
            <div class="flex flex-col items-center justify-center gap-1">
                {{-- Holiday --}}
                @if ($dayData && data_get($dayData, 'is_holiday'))
                    @php
                        $isHalfDayHoliday = data_get($dayData, 'is_half_day_holiday');
                    @endphp

                    <div
                        x-cloak
                        x-show="! isStatusHidden('holiday')"
                        class="relative flex size-10 items-center justify-center overflow-hidden rounded font-semibold text-white"
                        style="
                            background-color: {{ data_get($absenceTypes, 'holiday.color', '#fee685') }};
                        "
                    >
                        <span class="truncate px-0.5 text-[10px]">
                            {{ data_get($absenceTypes, 'holiday.icon', '🎉') }}
                        </span>
                        @if ($isHalfDayHoliday)
                            <div
                                class="absolute inset-0 bg-white/40"
                                style="
                                    clip-path: polygon(
                                        100% 0,
                                        0 100%,
                                        100% 100%
                                    );
                                "
                                title="{{ data_get($dayData, 'holiday_day_part') === 'first_half' ? __('First Half') : __('Second Half') }}"
                            ></div>
                            <span
                                class="absolute bottom-0 right-0.5 text-[8px] font-bold opacity-80"
                            >
                                ½
                            </span>
                        @endif
                    </div>
                @endif

                {{-- Present (actual hours > 0) --}}
                @if ($dayData && data_get($dayData, 'actual_hours', 0) > 0)
                    <div
                        x-on:click="$wire.showEmployeeDay({{ data_get($dayData, 'id', 'null') }})"
                        x-cloak
                        x-show="! isStatusHidden('present')"
                        class="{{ data_get($dayData, 'plus_minus_overtime_hours', 0) > 0 ? 'ring-2 ring-green-400 ring-offset-1 dark:ring-green-600' : '' }} {{ data_get($dayData, 'plus_minus_overtime_hours', 0) < 0 ? 'ring-2 ring-red-400 ring-offset-1 dark:ring-red-600' : '' }} flex size-10 items-center justify-center rounded border-green-500 bg-green-500 text-green-50 dark:border-transparent dark:bg-green-700 dark:bg-opacity-80"
                        style="
                            background-color: {{ data_get($absenceTypes, 'present.color', 'green') }};
                        "
                    >
                        {{ number_format(data_get($dayData, 'actual_hours'), 1) }}h
                    </div>
                @endif

                {{-- Currently Working --}}
                @if ($dayData && data_get($dayData, 'is_daily_work_time'))
                    <div
                        x-on:click="$wire.showWorkTime('{{ $dateKey }}')"
                        x-cloak
                        x-show="! isStatusHidden('working')"
                        class="flex size-10 animate-pulse items-center justify-center rounded font-semibold text-white"
                        style="
                            background-color: {{ data_get($absenceTypes, 'working.color', 'green') }};
                        "
                    >
                        {{ data_get($absenceTypes, 'working.icon', '▶') }}
                    </div>
                @endif

                {{-- Absence Requests --}}
                @if ($dayData && ! empty(data_get($dayData, 'absence_requests')))
                    @foreach (data_get($dayData, 'absence_requests') as $absence)
                        @php
                            $isHalfDay = data_get($absence, 'day_part') && data_get($absence, 'day_part') !== 'full_day';
                        @endphp

                        <div
                            x-on:click="$wire.showAbsenceRequest({{ data_get($absence, 'id') }})"
                            x-cloak
                            x-show="! isStatusHidden('{{ data_get($absence, 'absence_type_id') }}')"
                            class="relative flex size-10 items-center justify-center overflow-hidden rounded font-semibold text-white"
                            style="
                                background-color: {{ data_get($absenceTypes, data_get($absence, 'absence_type_id') . '.color', 'gray') }};
                            "
                        >
                            <span class="truncate px-0.5 text-[10px]">
                                {{ data_get($absenceTypes, data_get($absence, 'absence_type_id') . '.icon', '') }}
                            </span>
                            @if ($isHalfDay)
                                <div
                                    class="absolute inset-0 bg-white/40"
                                    style="
                                        clip-path: polygon(
                                            100% 0,
                                            0 100%,
                                            100% 100%
                                        );
                                    "
                                    title="{{ data_get($absence, 'day_part') === 'first_half' ? __('First Half') : __('Second Half') }}"
                                ></div>
                                <span
                                    class="absolute bottom-0 right-0.5 text-[8px] font-bold opacity-80"
                                >
                                    ½
                                </span>
                            @endif
                        </div>
                    @endforeach
                @endif

                {{-- Unexcused Absence (also show for half-day holidays where no work was done) --}}
                @if ($dayData && data_get($dayData, 'is_work_day') && empty(data_get($dayData, 'absence_requests')) && data_get($dayData, 'actual_hours', 0) == 0 && (! data_get($dayData, 'is_holiday') || data_get($dayData, 'is_half_day_holiday')) && ! data_get($calDay, 'isFuture') && ! data_get($calDay, 'isToday'))
                    <div
                        x-on:click="{{ data_get($dayData, 'id') ? "\$wire.showEmployeeDay(" . data_get($dayData, 'id') . ')' : '' }}"
                        x-cloak
                        x-show="! isStatusHidden('absent')"
                        class="flex size-10 items-center justify-center rounded bg-red-500 text-red-50 dark:border-transparent dark:bg-red-700 dark:bg-opacity-80"
                        style="
                            background-color: {{ data_get($absenceTypes, 'absent.color', 'red') }};
                        "
                    >
                        {{ number_format(data_get($dayData, 'plus_minus_overtime_hours', 0), 1) }}h
                    </div>
                @endif
            </div>
        </td>
    @endforeach
</tr>
