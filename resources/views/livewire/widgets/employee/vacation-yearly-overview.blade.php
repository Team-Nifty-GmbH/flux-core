<div>
    <x-table>
        <x-slot:header>
            <tr>
                <th
                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                >
                    {{ __('Year') }}
                </th>
                <th
                    class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                >
                    {{ __('Carryover') }}
                </th>
                <th
                    class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                >
                    {{ __('Earned') }}
                </th>
                <th
                    class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                >
                    {{ __('Adjustments') }}
                </th>
                <th
                    class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                >
                    {{ __('Available') }}
                </th>
                <th
                    class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                >
                    {{ __('Requested') }}
                </th>
                <th
                    class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                >
                    {{ __('Used') }}
                </th>
                <th
                    class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                >
                    {{ __('Remaining') }}
                </th>
            </tr>
        </x-slot>

        @forelse ($yearlyData as $year)
            <tr
                @class([
                    'bg-primary-50 dark:bg-primary-900/20' => $year['is_current'],
                ])
            >
                <td class="whitespace-nowrap px-4 py-2">
                    <span
                        @class([
                            'font-semibold' => $year['is_current'],
                        ])
                    >
                        {{ $year['year'] }}
                        @if ($year['employment_date'])
                            <div
                                class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ __('Entry') }}:
                                {{ $year['employment_date'] }}
                            </div>
                        @endif

                        @if ($year['termination_date'])
                            <div
                                class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ __('Exit') }}:
                                {{ $year['termination_date'] }}
                            </div>
                        @endif
                    </span>
                </td>
                <td class="whitespace-nowrap px-4 py-2 text-right">
                    @if ($year['carryover_days'] != '0.0')
                        <span class="font-medium">
                            {{ $year['carryover_days'] }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('days') }}
                        </span>
                    @else
                        <span class="text-gray-400 dark:text-gray-600">-</span>
                    @endif
                </td>
                <td class="whitespace-nowrap px-4 py-2 text-right">
                    <span class="font-medium">{{ $year['earned_days'] }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('days') }}
                    </span>
                </td>
                <td class="whitespace-nowrap px-4 py-2 text-right">
                    @if ($year['adjustments_days'] != '0.0')
                        <span class="font-medium">
                            {{ $year['adjustments_days'] }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('days') }}
                        </span>
                    @else
                        <span class="text-gray-400 dark:text-gray-600">-</span>
                    @endif
                </td>
                <td class="whitespace-nowrap px-4 py-2 text-right">
                    <span
                        class="font-semibold text-blue-600 dark:text-blue-400"
                    >
                        {{ $year['available_days'] }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('days') }}
                    </span>
                </td>
                <td class="whitespace-nowrap px-4 py-2 text-right">
                    <span class="font-medium">
                        {{ $year['requested_days'] }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('days') }}
                    </span>
                </td>
                <td class="whitespace-nowrap px-4 py-2 text-right">
                    <span class="font-medium">{{ $year['used_days'] }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('days') }}
                    </span>
                </td>
                <td class="whitespace-nowrap px-4 py-2 text-right">
                    <div
                        @class([
                            'text-green-600 dark:text-green-400' =>
                                (float) str_replace(',', '', $year['remaining_days']) > 0,
                            'text-red-600 dark:text-red-400' =>
                                (float) str_replace(',', '', $year['remaining_days']) < 0,
                            'font-semibold' => $year['is_current'],
                        ])
                    >
                        <span class="font-medium">
                            {{ $year['remaining_days'] }}
                        </span>
                        <span class="text-xs">{{ __('days') }}</span>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td
                    colspan="8"
                    class="px-4 py-2 text-center text-gray-500 dark:text-gray-400"
                >
                    {{ __('No vacation data available') }}
                </td>
            </tr>
        @endforelse

        @if (count($yearlyData) > 0)
            <x-slot:footer>
                <tr class="bg-gray-50 font-semibold dark:bg-gray-800">
                    <td class="px-4 py-3 text-left">
                        {{ __('Summary') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-gray-400 dark:text-gray-600">-</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @php
                            $totalEarnedDays = collect($yearlyData)->sum(fn ($y) => (float) str_replace(',', '.', $y['earned_days']));
                        @endphp

                        {{ Number::format($totalEarnedDays, 2) }}
                        <span class="text-xs">{{ __('days') }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @php
                            $totalAdjustmentsDays = collect($yearlyData)->sum(fn ($y) => (float) str_replace(',', '.', $y['adjustments_days']));
                        @endphp

                        @if ($totalAdjustmentsDays != 0)
                            {{ Number::format($totalAdjustmentsDays, 2) }}
                            <span class="text-xs">{{ __('days') }}</span>
                        @else
                            <span class="text-gray-400 dark:text-gray-600">
                                -
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-gray-400 dark:text-gray-600">-</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @php
                            $totalRequestedDays = collect($yearlyData)->sum(fn ($y) => (float) str_replace(',', '.', $y['requested_days']));
                        @endphp

                        {{ Number::format($totalRequestedDays, 2) }}
                        <span class="text-xs">{{ __('days') }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @php
                            $totalUsedDays = collect($yearlyData)->sum(fn ($y) => (float) str_replace(',', '.', $y['used_days']));
                        @endphp

                        {{ Number::format($totalUsedDays, 2) }}
                        <span class="text-xs">{{ __('days') }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @php
                            $currentBalanceDays = $yearlyData[0]['remaining_days'] ?? '0';
                            $currentBalanceFloat = (float) str_replace(',', '', $currentBalanceDays);
                        @endphp

                        <div
                            @class([
                                'text-green-600 dark:text-green-400' => $currentBalanceFloat > 0,
                                'text-red-600 dark:text-red-400' => $currentBalanceFloat < 0,
                            ])
                        >
                            {{ $currentBalanceDays }}
                            <span class="text-xs">{{ __('days') }}</span>
                        </div>
                    </td>
                </tr>
            </x-slot>
        @endif
    </x-table>
</div>
