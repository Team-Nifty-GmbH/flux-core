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

    if ($warehouseId !== null) {
        $where[] = ['warehouse_id', '=', $warehouseId];
    }

    if ($excludeId !== null) {
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
    :label="$label ?? __('Warehouse Bin')"
    :required="$required"
    :hint="$hint"
    select="label:label|value:id"
    unfiltered
    :request="[
        'url' => route('search', \FluxErp\Models\WarehouseBin::class),
        'method' => 'POST',
        'params' => [
            'searchFields' => ['code', 'name'],
            'where' => $where,
        ],
    ]"
/>
