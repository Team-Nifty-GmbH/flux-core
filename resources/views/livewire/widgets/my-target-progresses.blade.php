@extends('flux::livewire.support.widgets.charts.chart')
@section('options')
    <div class="pr-4">
        <x-select.styled
            autocomplete="off"
            wire:model.live="targetId"
            select="label:name|value:id"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\Target::class),
                'method' => 'POST',
                'params' => [
                    'where' => [
                        [
                            'parent_id',
                            '=',
                            null,
                        ],
                    ],
                    'whereHas' => [
                        'users' => [
                            ['user_id', '=', $this->userId],
                        ],
                    ],
                    'whereBetween' => [
                        'start_date', [$this->start, $this->end]
                    ],
                    'whereBetween' => [
                        'end_date', [$this->start, $this->end]
                    ],
                ],
            ]"
        />
    </div>
@endsection
