@extends('flux::livewire.charts.chart')
@section('options')
    @parent
    <x-native-select
        x-model="options.chart.type"
        :options="[
                [
                    'value' => 'bar',
                    'label' => __('Bar'),
                ],
                [
                    'value' => 'line',
                    'label' => __('Line'),
                ],
                [
                    'value' => 'area',
                    'label' => __('Area'),
                ],
            ]"
        option-value="value"
        option-label="label"
    >
    </x-native-select>
@endsection
@if($showTotals)
    @section('chart')
        @parent
        <div class="flex gap-1.5 justify-between w-full border p-1.5 rounded">
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
