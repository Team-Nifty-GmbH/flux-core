@extends('flux::livewire.charts.chart')

@if($showTotals)
    @section('chart')
        @parent
        <div class="flex gap-1.5 justify-between w-full border p-1.5">
            <template x-for="seriesData in options.series">
                <div class="flex flex-col gap-2 items-center font-semibold">
                    <div x-text="seriesData.name"></div>
                    <div x-html="window.formatters.badge(window.formatters.money(seriesData.sum), seriesData.colorName)">
                    </div>
                </div>
            </template>
        </div>
    @endsection
@endif
