@extends('flux::livewire.charts.chart')
@section('options')
    @parent
    <x-native-select
        x-model="options.chart.type"
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
