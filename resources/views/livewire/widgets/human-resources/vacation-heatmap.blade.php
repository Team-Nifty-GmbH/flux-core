<div class="flex h-full flex-col gap-4 p-4">
    <div>
        <h2
            class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400"
        >
            {{ __('Vacation Heatmap') }}
        </h2>
        <hr class="mt-2" />
    </div>
    <div class="overflow-auto">
        <div class="mb-1 grid grid-cols-7 gap-1">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                <div class="text-center text-xs font-medium text-gray-500">
                    {{ __($day) }}
                </div>
            @endforeach
        </div>
        @foreach($weeks as $week)
            <div class="mb-1 grid grid-cols-7 gap-1">
                @foreach($week as $day)
                    @if($day)
                        <div
                            @class([
                                'rounded p-1 text-center text-xs',
                                'bg-gray-50 dark:bg-gray-800 text-gray-400' => $day['is_weekend'],
                                'ring-2 ring-primary-500' => $day['is_today'],
                                'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' => ! $day['is_weekend'] && $day['absent_count'] === 0,
                                'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' => ! $day['is_weekend'] && $day['absent_count'] > 0 && $day['percentage'] < 20,
                                'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300' => ! $day['is_weekend'] && $day['percentage'] >= 20 && $day['percentage'] < 40,
                                'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' => ! $day['is_weekend'] && $day['percentage'] >= 40,
                            ])
                            title="{{ $day['date_formatted'] }}: {{ $day['absent_count'] }} {{ __('absent') }}"
                        >
                            <div class="font-medium">
                                {{ $day['day_number'] }}
                            </div>
                            @unless($day['is_weekend'])
                                <div class="text-[10px]">
                                    {{ $day['absent_count'] }}
                                </div>
                            @endunless
                        </div>
                    @else
                        <div></div>
                    @endif
                @endforeach
            </div>
        @endforeach
        <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
            <span class="flex items-center gap-1">
                <span
                    class="h-3 w-3 rounded bg-green-100 dark:bg-green-900/30"
                ></span>
                0%
            </span>
            <span class="flex items-center gap-1">
                <span
                    class="h-3 w-3 rounded bg-yellow-100 dark:bg-yellow-900/30"
                ></span>
                &lt;20%
            </span>
            <span class="flex items-center gap-1">
                <span
                    class="h-3 w-3 rounded bg-orange-100 dark:bg-orange-900/30"
                ></span>
                20-40%
            </span>
            <span class="flex items-center gap-1">
                <span
                    class="h-3 w-3 rounded bg-red-100 dark:bg-red-900/30"
                ></span>
                &gt;40%
            </span>
        </div>
    </div>
</div>
