<x-card x-data="apex_chart" class="text-sm w-full flex flex-col justify-center gap-4">
    <x-spinner />
    <div class="flex flex-1 justify-end gap-4">
        @section('options')
            <x-select
                :options="$timeFrames"
                option-value="value"
                option-label="label"
                wire:model.live="timeFrame"
                :clearable="false"
            />
        @show
    </div>
    <div class="flex flex-col flex-grow justify-between gap-4">
        @section('chart')
            <div class="chart w-full">
            </div>
        @show
    </div>
</x-card>
