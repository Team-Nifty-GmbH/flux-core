<?php

namespace FluxErp\Jobs;

use FluxErp\Invokable\ProcessSubscriptionOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class ProcessSubscriptionOrderJob extends ProcessSubscriptionOrder implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(private readonly int|string $orderId, private readonly int|string $orderTypeId) {}

    public function uniqueId(): string
    {
        return static::class . ':' . $this->orderId;
    }

    public function handle(): void
    {
        $this->__invoke($this->orderId, $this->orderTypeId);
    }
}
