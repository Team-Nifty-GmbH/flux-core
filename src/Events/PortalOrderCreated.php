<?php

namespace FluxErp\Events;

use FluxErp\Models\Order;
use FluxErp\Support\Event\SubscribableEvent;

class PortalOrderCreated extends SubscribableEvent
{
    public function __construct(public Order $order) {}
}
