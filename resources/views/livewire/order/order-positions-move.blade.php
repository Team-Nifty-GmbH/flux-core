<x-flux::features.sortable.sortable
    :data="$orderPositions"
    x-sort="$wire.reOrder($item, $position)"
    x-sort:group="order-positions"
    x-sort:config="{fallbackOnBody: true, swapThreshold: 0.65}"
/>
