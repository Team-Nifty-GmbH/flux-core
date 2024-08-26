@extends('flux::livewire.charts.chart')
@section('options')
    @parent
    @if($chartTypes)
        <x-native-select
            x-model="chartType"
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
    @endif
@endsection
@if($showTotals)
    @section('chart')
        <div class="px-6">
            <div class="md:flex gap-1.5 justify-between w-full rounded h-20">
                <template x-for="seriesData in livewireOptions.series">
                    <div class="flex flex-col gap-2 items-center font-semibold">
                        <div x-text="seriesData.name"></div>
                        <div x-html="window.formatters.badge(window.formatters.money(seriesData.sum), seriesData.colorName)">
                        </div>
                    </div>
                </template>
            </div>
            <hr>
        </div>
        @parent
    @endsection
@endif
