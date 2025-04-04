@props([
    "position",
])
<div
    id="{{ data_get($position, "id") }}"
    @if (! $this->order->is_locked)
        x-sort:item="{{ data_get($position, "id") }}"
    @endif
    x-data="{ record: @js($position) }"
    class="position-item mb-2 text-sm"
>
    <div
        class="rounded-md border bg-white p-3 shadow-sm"
        @if (! $this->order->is_locked)
            x-bind:class="{
                'cursor-move':
                    ! {{ data_get($position, "is_bundle_position") ? "true" : "false" }},
                'cursor-ns-resize':
                    {{ data_get($position, "is_bundle_position") ? "true" : "false" }},
            }"
        @endif
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                @if (data_get($position, "is_free_text") || count(data_get($position, "children", [])) > 0)
                    <button
                        x-on:click="toggleExpand({{ data_get($position, "id") }})"
                        type="button"
                        class="text-gray-500 hover:text-gray-700 focus:outline-none"
                    >
                        <x-icon
                            name="chevron-right"
                            class="h-4 w-4 transform transition-transform"
                            x-bind:class="{'rotate-90': isExpanded({{ data_get($position, 'id') }})}"
                        />
                    </button>
                @else
                    <div class="w-5"></div>
                @endif

                <div>
                    {!! data_get($position, "slug_position") . " " . data_get($position, "name") !!}
                </div>
            </div>

            <div class="flex items-center space-x-4">
                @if (data_get($position, "amount") && data_get($position, "unit_price"))
                    <div class="text-sm text-gray-600">
                        {{ is_null(data_get($position, "total_net_price")) ? null : data_get($position, "amount") . " × " . Number::currency(data_get($position, "unit_price"), data_get($this->order->currency, "iso"), app()->getLocale()) }}
                    </div>
                @endif

                <div class="font-semibold">
                    {{
                        is_null(data_get($position, "total_net_price"))
                            ? null
                            : Number::currency(data_get($position, "total_net_price"), data_get($this->order->currency, "iso"), app()->getLocale())
                    }}
                </div>
                <div>
                    @foreach ($rowActions ?? [] as $rowAction)
                        {{ $rowAction }}
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div
        x-cloak
        x-show="isExpanded({{ data_get($position, "id") }})"
        x-collapse
        class="mt-2 border-l-2 border-gray-200 pl-6"
    >
        <div
            class="nested-sortable space-y-2"
            data-parent-id="{{ data_get($position, "id") }}"
            @if (! $this->order->is_locked)
                x-sort="$wire.movePosition($item, $position, {{ data_get($position, "id") }})"
                x-sort:group="{{ data_get($position, "is_free_text") ? "nested-positions" : "bundle-positions-" . data_get($position, "id") }}"
            @endif
        >
            @if (count($children = data_get($position, "children", [])) > 0)
                @foreach ($children as $child)
                    <x-flux::order.order-position-sort-item
                        :position="$child"
                        :row-actions="$rowActions"
                    />
                @endforeach
            @elseif (data_get($position, "is_free_text"))
                <div
                    class="empty-sort-placeholder flex h-8 items-center justify-center rounded-md border border-dashed border-gray-300"
                >
                    <span class="text-gray-400">
                        {{ __("Drop items here…") }}
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>
