@extends('flux::support.widgets.charts.chart')
@section('options')
    @parent
    <x-native-select
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
        option-value="value"
        option-label="label"
    >
    </x-native-select>
@endsection
