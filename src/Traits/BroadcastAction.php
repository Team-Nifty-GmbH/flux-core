<?php

namespace FluxErp\Traits;

use FluxErp\Actions\FluxAction;
use FluxErp\Events\BroadcastActionExecuted;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PendingBroadcast;
use Illuminate\Contracts\Broadcasting\HasBroadcastChannel;
use Illuminate\Support\Arr;

trait BroadcastAction
{
    protected static bool $isBroadcasting = true;

    public static function bootBroadcastAction(): void
    {
        static::executed(function (FluxAction $action) {
            $action->broadcastAction();
        });
    }

    public function broadcastAction(Channel|HasBroadcastChannel|array|null $channels = null): ?PendingBroadcast
    {
        return $this->broadcastIfBroadcastChannelsExists(
            $this->broadcastActionExecuted(), $channels
        );
    }

    protected function broadcastIfBroadcastChannelsExists(
        BroadcastActionExecuted $instance,
        mixed $channels = null): ?PendingBroadcast
    {
        if (! static::$isBroadcasting
            || (empty($this->broadcastOn()) && empty($channels))
        ) {
            return null;
        }

        return broadcast($instance->onChannels(Arr::wrap($channels)));
    }

    public function broadcastActionExecuted(): mixed
    {
        return tap(new BroadcastActionExecuted(get_class($this), $this->getData(), $this->result), function ($event) {
            $event->connection = $this->broadcastConnection();
            $event->queue = $this->broadcastQueue();
        });
    }

    public function broadcastOn(): Channel|array
    {
        return [$this];
    }

    public function broadcastConnection()
    {
        //
    }

    public function broadcastQueue()
    {
        //
    }
}
