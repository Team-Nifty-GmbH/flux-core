<?php

namespace FluxErp\Events\Order;

use FluxErp\Models\Order;
use FluxErp\Support\Event\SubscribableEvent;
use Illuminate\Queue\SerializesModels;

class OrderApprovalRequestEvent extends SubscribableEvent
{
    use SerializesModels;

    public function __construct(public Order $order) {}
}
