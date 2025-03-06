<div x-data="apexCharts($wire)" class="pt-4 pb-4 text-sm max-h-full h-full w-full flex flex-col gap-4">
    @if($withSpinner) <x-flux::spinner /> @endif
    <div>
        @section('title')
            @if($this->showTitle())
                <div class="flex justify-between w-full px-6">
                    <h2 class="truncate text-lg font-semibold text-gray-400">{{ $this->getLabel() }}</h2>
                </div>
                <hr class="mx-6">
            @endif
        @show
        @section('options')
        @show
    </div>
    <div class="h-full flex-1 flex flex-col flex-grow dark:text-gray-400 justify-between gap-4">
        @section('chart')
            <div class="chart w-full h-full">
            </div>
        @show
    </div>
</div>
