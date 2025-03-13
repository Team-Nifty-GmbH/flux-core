@extends("flux::support.widgets.charts.chart")
@section("options")
    @parent
    @if ($chartTypes)
        <x-select.native
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
        ></x-select.native>
    @endif
@endsection

@if ($showTotals)
    @section("chart")
        <div class="px-6">
            <div
                class="soft-scrollbar h-20 w-full gap-12 overflow-x-auto rounded md:flex"
            >
                <template
                    x-for="seriesData in livewireOptions.series?.filter((series) => ! series.hideFromTotals)"
                >
                    <div class="flex flex-col gap-2">
                        <div
                            class="whitespace-nowrap text-lg font-semibold"
                            x-text="seriesData.name"
                        ></div>
                        <div class="flex gap-2">
                            <div
                                class="text-2xl font-bold"
                                x-bind:class="seriesData.colorName"
                                x-text="
                                    typeof defaultOptions.dataLabels.formatter === 'function'
                                        ? defaultOptions.dataLabels.formatter(seriesData.sum)
                                        : seriesData.sum
                                "
                            ></div>
                            <x-badge
                                x-cloak
                                x-show="!isNaN(seriesData.growthRate) && seriesData.growthRate > 0"
                                icon="chevron-up"
                                color="emerald"
                            >
                                <span
                                    x-text="seriesData.growthRate + '%'"
                                ></span>
                            </x-badge>
                            <x-badge
                                x-cloak
                                x-show="!isNaN(seriesData.growthRate) && seriesData.growthRate < 0"
                                icon="chevron-down"
                                color="red"
                            >
                                <span
                                    x-text="seriesData.growthRate + '%'"
                                ></span>
                            </x-badge>
                            <x-badge
                                x-cloak
                                x-show="!isNaN(seriesData.growthRate) && seriesData.growthRate == 0"
                                icon="chevron-right"
                                color="gray"
                            >
                                <span
                                    x-text="seriesData.growthRate + '%'"
                                ></span>
                            </x-badge>
                        </div>
                    </div>
                </template>
            </div>
            <hr />
        </div>
        @parent
    @endsection
@endif
