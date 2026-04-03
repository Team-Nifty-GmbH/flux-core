@extends ('flux::livewire.support.widgets.charts.chart')

@section ('options')
    @if ($showDonutOptions ?? true)
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
    @endif
    @parent
@endsection
