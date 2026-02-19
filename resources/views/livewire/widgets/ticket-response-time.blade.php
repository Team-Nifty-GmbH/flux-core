<div class="flex h-full flex-col gap-4 p-4">
    <h2 class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400">
        {{ $this->getLabel() }}
    </h2>
    <hr />
    <div class="flex flex-1 items-center justify-around gap-6">
        @if ($firstResponseHours !== null || $resolutionHours !== null)
            <div class="flex flex-col items-center gap-1">
                <span class="text-3xl font-bold {{ $firstResponseColor }}">
                    {{ $firstResponseFormatted }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Avg. First Response') }}
                </span>
            </div>
            <div class="flex flex-col items-center gap-1">
                <span class="text-3xl font-bold {{ $resolutionColor }}">
                    {{ $resolutionFormatted }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Avg. Resolution') }}
                </span>
            </div>
        @else
            <div class="text-gray-500 dark:text-gray-400">
                {{ __('No data') }}
            </div>
        @endif
    </div>
</div>
