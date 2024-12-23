<div x-data="apexCharts($wire)" class="pt-4 pb-4 text-sm max-h-full h-full w-full flex flex-col gap-4">
    @if($withSpinner) <x-flux::spinner /> @endif
    <div class="h-full flex-1 flex flex-col flex-grow dark:text-gray-400 justify-between gap-4">
        @section('chart')
            <div class="chart w-full h-full">
            </div>
        @show
    </div>
</div>
