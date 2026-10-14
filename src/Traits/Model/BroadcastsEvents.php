<?php

namespace FluxErp\Traits\Model;

use FluxErp\Events\BroadcastableModelEventOccurred;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PendingBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents as BaseBroadcastsEvents;
use function Illuminate\Support\defer;

trait BroadcastsEvents
{
    use BaseBroadcastsEvents {
        BaseBroadcastsEvents::broadcastWith as protected baseBroadcastWith;
        BaseBroadcastsEvents::bootBroadcastsEvents as protected baseBootBroadcastsEvents;
    }

    protected static bool $broadcastOnlyKey = true;

    protected static bool $withoutBroadcasting = false;

    public static function bootBroadcastsEvents(): void
    {
        if (static::getWithoutBroadcasting()) {
            return;
        }

        static::baseBootBroadcastsEvents();
    }

    public static function getBroadcastOnlyKey(): bool
    {
        return static::$broadcastOnlyKey;
    }

    public static function getGenericChannelEvents(): array
    {
        return ['created'];
    }

    public static function getModelChannelEvents(): array
    {
        return ['updated', 'deleted', 'trashed', 'restored'];
    }

    public static function getWithoutBroadcasting(): bool
    {
        return static::$withoutBroadcasting;
    }

    public static function recordBroadcastChannel(int|string $key): string
    {
        return static::getBroadcastChannel() . $key;
    }

    public function broadcastChannel(): string
    {
        return $this->getMorphClass() . '.' . $this->getKey();
    }

    public function broadcastEvent(string $event, $channels = null, bool $toEveryone = false): ?PendingBroadcast
    {
        /** @var BroadcastableModelEventOccurred $eventClass */
        $eventClass = $this->newBroadcastableModelEvent($event);

        if ($toEveryone) {
            $eventClass->broadcastToEveryone();
        } else {
            $eventClass->dontBroadcastToCurrentUser();
        }

        return $this->broadcastIfBroadcastChannelsExistForEvent($eventClass, $event, $channels);
    }

    public function broadcastOn(string $event): array|Channel
    {
        $channel = $this->broadcastChannel();
        $modelChannel = Str::beforeLast($channel, '.') . '.';

        if (in_array($event, static::getGenericChannelEvents())) {
            return new PrivateChannel($modelChannel);
        }

        return in_array($event, static::getModelChannelEvents())
            ? [new PrivateChannel($channel), new PrivateChannel($modelChannel)]
            : new PrivateChannel($channel);
    }

    public function broadcastWith(): array
    {
        return static::getBroadcastOnlyKey() && method_exists($this, 'getKey')
            ? ['model' => [$this->getKeyName() => $this->getKey()]]
            : $this->baseBroadcastWith();
    }

    protected function broadcastIfBroadcastChannelsExistForEvent(
        $instance,
        $event,
        $channels = null
    ): ?PendingBroadcast {
        if (! static::$isBroadcasting) {
            return null;
        }

        if ($instance instanceof BroadcastableModelEventOccurred) {
            $instance->broadcastNow();
        }

        $channels = Arr::wrap($channels ?? $this->broadcastOn($event));

        defer(
            function () use ($instance, $channels): void {
                if ($channels) {
                    broadcast($instance->onChannels($channels));
                }
            },
            always: true
        );

        return null;
    }

    protected function broadcastToEveryone(): bool
    {
        return false;
    }

    protected function newBroadcastableEvent(string $event): BroadcastableModelEventOccurred
    {
        $event = new BroadcastableModelEventOccurred($this, $event);

        if ($this->broadcastToEveryone()) {
            $event->broadcastToEveryone();
        } else {
            $event->dontBroadcastToCurrentUser();
        }

        return $event;
    }

    protected function recordBroadcastChannels(string $model, array $keys): array
    {
        return array_map(
            fn (int|string $key): PrivateChannel => new PrivateChannel(
                resolve_static($model, 'recordBroadcastChannel', ['key' => $key])
            ),
            array_values(array_unique(array_filter($keys)))
        );
    }
}
