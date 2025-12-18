@extends('flux::livewire.support.widgets.charts.chart')

@section('options')
    @if ($showDonutOptions ?? true)
        <div class="p-2">
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
    @endif

    @parent
@endsection
