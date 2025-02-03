@extends('flux::support.widgets.charts.bar-chart')
@section('options')
    <div class="px-6">
        <x-select
            option-value="id"
            option-label="label"
            autocomplete="off"
            wire:model.live="userId"
            :template="[
                'name'   => 'user-option',
            ]"
            :async-data="[
                'api' => route('search', \FluxErp\Models\User::class),
                'method' => 'POST',
                'params' => [
                    'with' => 'media',
                ],
            ]"
        />
    </div>
@endsection
