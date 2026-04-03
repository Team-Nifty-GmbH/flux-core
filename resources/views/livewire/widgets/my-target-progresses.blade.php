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
                    'whereNull' => [
                        'parent_id',
                    ],
                    'whereRelation' => [
                        'users',
                        'user_id',
                        '=',
                        $this->userId,
                    ],
                    'where' => [
                        [
                            'start_date',
                            '<=',
                            $this->end,
                        ],
                        [
                            'end_date',
                            '>=',
                            $this->start,
                        ],
                    ],
                ],
            ]"
        />
    </div>
@endsection
