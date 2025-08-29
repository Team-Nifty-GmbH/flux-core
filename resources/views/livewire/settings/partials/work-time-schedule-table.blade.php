<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-gray-50 dark:bg-gray-800">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                {{ __('Day') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                {{ __('Start Time') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                {{ __('End Time') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                {{ __('Work Hours') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                {{ __('Break (Minutes)') }}
            </th>
        </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
        @php
            $days = [
                1 => __('Monday'),
                2 => __('Tuesday'),
                3 => __('Wednesday'),
                4 => __('Thursday'),
                5 => __('Friday'),
                6 => __('Saturday'),
                7 => __('Sunday'),
            ];
        @endphp

        @foreach($days as $dayNum => $dayName)
            @php
                $dayData = $week['days'][$dayNum] ?? [
                    'weekday' => $dayNum,
                    'start_time' => null,
                    'end_time' => null,
                    'work_hours' => 0,
                    'break_minutes' => 0,
                ];
            @endphp
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $dayName }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    <input
                        type="time"
                        wire:change="updateSchedule({{ $weekIndex }}, {{ $dayNum }}, 'start_time', $event.target.value)"
                        value="{{ $dayData['start_time'] }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                    />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    <input
                        type="time"
                        wire:change="updateSchedule({{ $weekIndex }}, {{ $dayNum }}, 'end_time', $event.target.value)"
                        value="{{ $dayData['end_time'] }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                    />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    <input
                        type="number"
                        step="0.5"
                        min="0"
                        max="24"
                        wire:change="updateSchedule({{ $weekIndex }}, {{ $dayNum }}, 'work_hours', $event.target.value)"
                        value="{{ $dayData['work_hours'] }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                    />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    <input
                        type="number"
                        min="0"
                        max="480"
                        wire:change="updateSchedule({{ $weekIndex }}, {{ $dayNum }}, 'break_minutes', $event.target.value)"
                        value="{{ $dayData['break_minutes'] }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                    />
                </td>
            </tr>
        @endforeach
    </tbody>
</table>