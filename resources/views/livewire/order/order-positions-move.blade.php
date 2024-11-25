<x-flux::sortable
    :data="$orderPositions"
    x-sort="$wire.reOrder($item, $position)"
    x-sort:config="{fallbackOnBody: true, swapThreshold: 0.65}"
/>
