@extends('flux::support.widgets.charts.bar-chart')
@section('options')
    <div class="px-6">
        <x-select.styled
            autocomplete="off"
            wire:model.live="userId"
            :request="[
                'url' => route('search', \FluxErp\Models\User::class),
                'method' => 'POST',
                'params' => [
                    'with' => 'media',
                ],
            ]"
        />
    </div>
@endsection
