@props(['itemKey' => 'id', 'childrenKey' => 'children', 'label' => 'name', 'data' => []])
<ul
    {{ $attributes->only(['x-sort', 'x-sort:config']) }}
    class="flex flex-col gap-1.5"
>
    @foreach($data as $item)
        <li x-sort:item="{{ data_get($item, $itemKey) }}">
            <x-card>
                {{ data_get($item, 'name') }}
                @if(data_get($item, $childrenKey))
                    <div class="pt-4">
                        <x-flux::sortable
                            :$attributes
                            :data="data_get($item, $childrenKey)"
                        />
                    </div>
                @endif
            </x-card>
        </li>
    @endforeach
</ul>
