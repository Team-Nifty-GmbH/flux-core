@props(['itemKey' => 'id', 'childrenKey' => 'children', 'label' => 'name', 'data' => [], 'level' => 0])
<ul
    {{ $attributes->only(['x-sort', 'x-sort:config']) }}
    class="flex flex-col gap-1.5 bg-white rounded shadow-sm px-4 py-2"
    x-sort:group="{{ $level > 0 && array_sum(array_column(data_get($data, $childrenKey, []), 'is_bundle_position')) === count(data_get($data, $childrenKey, [])) ? 'bundle-positions' : 'positions' }}"
>
    @foreach($data as $item)
        <x-flux::features.sortable.sortable-item
            :$attributes
            :item="$item"
            :item-key="$itemKey"
            :children-key="$childrenKey"
            :label="$label"
            :level="$level"
        />
    @endforeach
</ul>
