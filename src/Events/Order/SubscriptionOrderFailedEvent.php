<?php

namespace FluxErp\Events\Order;

use FluxErp\Models\Order;
use FluxErp\Support\Event\SubscribableEvent;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SubscriptionOrderFailedEvent extends SubscribableEvent
{
    use SerializesModels;

    public function __construct(public Order $order, public Throwable $exception) {}
}
