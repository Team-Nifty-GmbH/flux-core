<?php

namespace FluxErp\Events\Order;

use FluxErp\Models\Order;

class OrderApprovalRequestEvent
{
    public function __construct(public Order $order) {}

    public function broadcastChannel(): string
    {
        return $this->order->broadcastChannel();
    }
}
