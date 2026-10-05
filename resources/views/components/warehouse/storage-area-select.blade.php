@props([
    'model',
    'label' => null,
    'warehouseId' => null,
    'excludeId' => null,
    'storageLocationOnly' => false,
    'activeOnly' => true,
    'required' => false,
    'hint' => null,
])

@php
    $where = [];

    if (! is_null($warehouseId)) {
        $where[] = ['warehouse_id', '=', $warehouseId];
    }

    if (! is_null($excludeId)) {
        $where[] = ['id', '!=', $excludeId];
    }

    if ($storageLocationOnly) {
        $where[] = ['is_storage_location', '=', true];
    }

    if ($activeOnly) {
        $where[] = ['is_active', '=', true];
    }
@endphp

<x-select.styled
    :wire:model="$model"
    :label="$label ?? __('Storage Area')"
    :required="$required"
    :hint="$hint"
    select="label:label|value:id"
    unfiltered
    :request="[
        'url' => route('search', \FluxErp\Models\StorageArea::class),
        'method' => 'POST',
        'params' => [
            'searchFields' => ['code', 'name'],
            'where' => $where,
        ],
    ]"
/>
