<?php

namespace FluxErp\Support\Broadcasting;

use Closure;
use Illuminate\Broadcasting\BroadcastManager;

class FluxBroadcastManager extends BroadcastManager
{
    protected ?Closure $deferredChannels = null;

    protected ?object $deferredChannelsRegisteredOn = null;

    public function auth($request)
    {
        $this->registerDeferredChannels();

        return $this->driver()->auth($request);
    }

    public function deferChannels(Closure $register): void
    {
        $this->deferredChannels = $register;
        $this->deferredChannelsRegisteredOn = null;
    }

    public function registerDeferredChannels(): void
    {
        $driver = $this->driver();

        if (is_null($this->deferredChannels) || $this->deferredChannelsRegisteredOn === $driver) {
            return;
        }

        $this->deferredChannelsRegisteredOn = $driver;

        ($this->deferredChannels)($driver->getChannels());
    }
}
