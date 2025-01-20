<?php

namespace FluxErp\Events\Order;

use FluxErp\Models\Order;
use FluxErp\Support\Event\SubscribableEvent;

class OrderApprovalRequestEvent extends SubscribableEvent
{
    public function __construct(public Order $order) {}
}
