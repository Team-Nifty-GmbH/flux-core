@extends('flux::support.widgets.charts.chart')
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
            <div class="md:flex overflow-x-auto soft-scrollbar gap-12 w-full rounded h-20">
                <template x-for="seriesData in livewireOptions.series?.filter((series) => ! series.hideFromTotals)">
                    <div class="flex flex-col gap-2">
                        <div class="font-semibold text-lg whitespace-nowrap" x-text="seriesData.name"></div>
                        <div class="flex gap-2">
                            <div
                                class="font-bold text-2xl"
                                x-bind:class="seriesData.colorName"
                                x-text="typeof defaultOptions.dataLabels.formatter === 'function'
                                    ? defaultOptions.dataLabels.formatter(seriesData.sum)
                                    : seriesData.sum"
                            >
                            </div>
                            <x-badge
                                x-cloak
                                x-show="!isNaN(seriesData.growthRate) && seriesData.growthRate > 0"
                                icon="chevron-up"
                                positive
                            >
                                <span x-text="seriesData.growthRate + '%'">
                                </span>
                            </x-badge>
                            <x-badge
                                x-cloak
                                x-show="!isNaN(seriesData.growthRate) && seriesData.growthRate < 0"
                                icon="chevron-down"
                                negative
                            >
                                <span x-text="seriesData.growthRate + '%'">
                                </span>
                            </x-badge>
                            <x-badge
                                x-cloak
                                x-show="!isNaN(seriesData.growthRate) && seriesData.growthRate == 0"
                                icon="chevron-right"
                                secondary
                            >
                                <span x-text="seriesData.growthRate + '%'">
                                </span>
                            </x-badge>
                        </div>
                    </div>
                </template>
            </div>
            <hr>
        </div>
        @parent
    @endsection
@endif
