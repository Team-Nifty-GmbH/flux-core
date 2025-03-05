@extends('flux::support.widgets.charts.chart')
@section('options')
    @parent
    <x-select.native
        x-model="chartType"
        :options="[
            [
                'value' => 'donut',
                'label' => __('Donut'),
            ],
            [
                'value' => 'polarArea',
                'label' => __('Polar Area'),
            ],
            [
                'value' => 'pie',
                'label' => __('Pie'),
            ],
        ]"
    >
    </x-select.native>
@endsection
