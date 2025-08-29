@extends('flux::livewire.widgets.my-target-progresses')
@section('options')
    <div class="px-2">
        <x-select.styled
            autocomplete="off"
            wire:model.live="userId"
            select="label:label|value:id"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\User::class),
                'method' => 'POST',
                'params' => [
                    'with' => 'media',
                    'whereHas' => [
                        'targets' => [
                            ['id', '=', $this->targetId],
                        ],
                    ],
                ],
            ]"
        />
    </div>
    @parent
@endsection
