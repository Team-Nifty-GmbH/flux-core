<?php

namespace FluxErp\Traits;

use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents as BaseBroadcastsEvents;

trait BroadcastsEvents
{
    use BaseBroadcastsEvents {
        BaseBroadcastsEvents::broadcastWith as protected baseBroadcastWith;
    }

    protected static bool $broadcastOnlyKey = false;

    public function broadcastWith(): array
    {
        return static::$broadcastOnlyKey && method_exists($this, 'getKey')
            ? [$this->getKeyName() => $this->getKey()]
            : $this->baseBroadcastWith();
    }
}
