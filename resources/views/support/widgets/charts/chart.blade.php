<div
    x-data="apexCharts($wire)"
    class="flex h-full max-h-full w-full flex-col gap-4 pb-4 pt-4 text-sm"
>
    @if ($withSpinner)
        <x-flux::spinner />
    @endif

    <div>
        @section('title')
        @if ($this->showTitle())
            <div class="flex w-full justify-between px-6">
                <h2 class="truncate text-lg font-semibold text-gray-400">
                    {{ $this->getLabel() }}
                </h2>
            </div>
            <hr class="mx-6" />
        @endif

        @show
        @section('options')
        @show
    </div>
    <div
        class="flex h-full flex-1 flex-grow flex-col justify-between gap-4 dark:text-gray-400"
    >
        @section('chart')
        <div class="chart h-full w-full"></div>
        @show
    </div>
</div>
