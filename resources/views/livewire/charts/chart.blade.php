<div x-data="apexCharts($wire)" class="pt-4 pb-4 text-sm max-h-full h-full w-full flex flex-col gap-4">
    <x-flux::spinner />
    <div class="flex justify-end items-center gap-4 pr-2">
        @section('options')
            <x-select
                class="p-2"
                :options="$timeFrames"
                option-value="value"
                option-label="label"
                wire:model.live="timeFrame"
                :clearable="false"
            />
        @show
    </div>
    <template x-if="$wire.timeFrame === 'Custom'">
        <div class="flex flex-col gap-1.5">
            <x-datetime-picker wire:model.live="start" without-time="true" :label="__('From')"/>
            <x-datetime-picker wire:model.live="end" without-time="true" :label="__('Until')"/>
        </div>
    </template>
    <div class="h-full flex-1 flex flex-col flex-grow dark:text-gray-400 justify-between gap-4">
        @section('chart')
            <div class="chart w-full h-full">
            </div>
        @show
    </div>
</div>
