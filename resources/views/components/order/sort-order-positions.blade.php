<div
    x-data="{
        expandedPositions: [],

        isExpanded(id) {
            return this.expandedPositions.includes(parseInt(id))
        },

        toggleExpand(id) {
            if (this.isExpanded(id)) {
                this.expandedPositions = this.expandedPositions.filter(
                    (item) => item !== parseInt(id),
                )
            } else {
                this.expandedPositions.push(parseInt(id))
            }
        },
    }"
>
    <div
        class="mt-2 space-y-2"
        @if (! $this->order->is_locked)
            x-sort="$wire.movePosition($item, $position)"
            x-sort:group="nested-positions"
        @endif
    >
        <x-flux::spinner />
        @foreach ($this->getSortableOrderPositions() as $position)
            <x-flux::order.order-position-sort-item
                :position="$position"
                :row-actions="$rowActions"
            />
        @endforeach
    </div>
</div>
