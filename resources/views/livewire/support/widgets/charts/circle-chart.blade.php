@extends('flux::livewire.support.widgets.charts.chart')
@section('options')
    @parent
    <div class="px-6 py-2">
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
        />
    </div>
@endsection
