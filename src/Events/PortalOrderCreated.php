<?php

namespace FluxErp\Events;

use FluxErp\Models\Order;

class PortalOrderCreated
{
    public function __construct(public Order $order) {}
}
