<?php

namespace FluxErp\Traits\Action;

use FluxErp\Actions\FluxAction;
use FluxErp\Events\BroadcastableActionEventOccurred;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PendingBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\HasBroadcastChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Serializable;

trait BroadcastsActionEvents
{
    protected static bool $isBroadcasting = true;

    public static function bootBroadcastsActionEvents(): void
    {
        static::executed(function (FluxAction $action) {
            $action->broadcastExecuted();
        });
    }

    public function broadcastExecuted(Channel|HasBroadcastChannel|array|null $channels = null): PendingBroadcast
    {
        return $this->broadcastEvent('executed', $channels);
    }

    protected function broadcastEvent(
        string $event,
        Channel|HasBroadcastChannel|array|null $channels = null
    ): ?PendingBroadcast {
        return $this->broadcastIfBroadcastChannelsExistForEvent(
            $this->newBroadcastableEvent($event), $event, $channels
        );
    }

    protected function broadcastIfBroadcastChannelsExistForEvent(
        $instance,
        string $event,
        Channel|HasBroadcastChannel|array|null $channels = null
    ): ?PendingBroadcast {
        if (! static::$isBroadcasting) {
            return null;
        }

        if (! empty($this->broadcastOn($event)) || ! empty($channels)) {
            return broadcast($instance->onChannels(Arr::wrap($channels)));
        }

        return null;
    }

    public function newBroadcastableEvent(string $event): BroadcastableActionEventOccurred
    {
        return new BroadcastableActionEventOccurred($this, $event);
    }

    public static function getBroadcastChannel(): string
    {
        return 'action.' . class_to_broadcast_channel(static::class, false);
    }

    public function broadcastOn(): array|Channel
    {
        return new PrivateChannel(static::getBroadcastChannel());
    }

    public function broadcastConnection(): ?string
    {
        //
    }

    public function broadcastQueue(): ?string
    {
        //
    }

    public function broadcastWith(string $event): array
    {
        if (! $this->getResult() instanceof Model) {
            $payload['result'] = $this->result instanceof Serializable
                ? $this->getResult()
                : null;

            return $payload;
        }

        $payload['result'] = [
            $this->getResult()->getKeyName() => $this->getResult()->getKey(),
            'model' => $this->getResult()->getMorphClass(),
        ];

        return $payload;
    }
}
