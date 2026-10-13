@props([
    'model',
    'preview' => false,
])

@if ($preview)
    <x-time
        :label="__('Time')"
        wire:model="{{ $model }}"
        wire:change="previewSchedule"
    />
@else
    <x-time :label="__('Time')" wire:model="{{ $model }}" />
@endif
