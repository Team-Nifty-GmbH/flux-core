<?php

namespace FluxErp\Support\Collection;

use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderCollection extends Collection
{
    public function printLayouts(): array
    {
        return array_keys($this->reduce(
            fn (?array $carry, Order $order) => array_merge(
                $carry ?? [],
                $order->resolvePrintViews()
            )
        ));
    }
}
