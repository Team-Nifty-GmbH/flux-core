<?php

namespace FluxErp\Events;

use Illuminate\Database\Eloquent\BroadcastableModelEventOccurred as BaseBroadcastableModelEventOccurred;

class BroadcastableModelEventOccurred extends BaseBroadcastableModelEventOccurred
{
    protected bool $broadcastNow = false;

    public function broadcastNow(): static
    {
        $this->broadcastNow = true;

        return $this;
    }

    public function shouldBroadcastNow(): bool
    {
        return $this->broadcastNow;
    }
}
