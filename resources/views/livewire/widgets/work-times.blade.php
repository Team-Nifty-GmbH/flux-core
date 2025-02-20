@extends('flux::support.widgets.charts.bar-chart')
@section('options')
    <div class="px-6">
        <x-select.styled
            select="label:label|value:id"
            autocomplete="off"
            wire:model.live="userId"
            :template="[
                'name'   => 'user-option',
            ]"
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
