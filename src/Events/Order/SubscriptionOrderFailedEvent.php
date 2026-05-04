<?php

namespace FluxErp\Events\Order;

use FluxErp\Models\Order;
use FluxErp\Support\Event\SubscribableEvent;
use Illuminate\Queue\SerializesModels;

class SubscriptionOrderFailedEvent extends SubscribableEvent
{
    use SerializesModels;

    public function __construct(
        public Order $order,
        public string $exceptionClass,
        public string $exceptionMessage,
        public array $validationErrors = [],
    ) {}
}
