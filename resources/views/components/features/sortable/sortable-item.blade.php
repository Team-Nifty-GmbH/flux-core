<li x-sort:item="{{ data_get($item, $itemKey) }}" class="cursor-ns-resize">
        {{ data_get($item, 'name') }}
        @if(count($children = data_get($item, $childrenKey, [])))
            <x-flux::features.sortable
                :$attributes
                :data="$children"
                :level="$level + 1"
            />
        @endif
</li>
