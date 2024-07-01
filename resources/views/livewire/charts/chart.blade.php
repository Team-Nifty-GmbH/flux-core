<div x-data="apex_chart" class="pt-4 pb-4 text-sm max-h-full w-full flex flex-col gap-4">
    <x-spinner />
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
    <div x-show="$wire.timeFrame === 'Custom'" x-transition class="flex flex-col gap-1.5">
        <x-datetime-picker wire:model.live="start" without-time="true" :label="__('From')"/>
        <x-datetime-picker wire:model.live="end" without-time="true" :label="__('Until')"/>
    </div>
    <div class="overflow-auto overflow-x-hidden flex-1 flex flex-col flex-grow justify-between gap-4">
        @section('chart')
            <div class="chart w-full">
            </div>
        @show
    </div>
</div>
