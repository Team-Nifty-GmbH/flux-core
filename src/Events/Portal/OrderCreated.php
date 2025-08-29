<?php

namespace FluxErp\Events\Portal;

use FluxErp\Models\Order;
use FluxErp\Support\Event\SubscribableEvent;
use Illuminate\Queue\SerializesModels;

class OrderCreated extends SubscribableEvent
{
    use SerializesModels;

    public function __construct(public Order $order) {}
}
