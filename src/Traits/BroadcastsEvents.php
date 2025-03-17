<?php

namespace FluxErp\Traits;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PendingBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\BroadcastableModelEventOccurred;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents as BaseBroadcastsEvents;

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

    public static function getWithoutBroadcasting(): bool
    {
        return static::$withoutBroadcasting;
    }

    public function broadcastChannel(): string
    {
        return parent::broadcastChannel();
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

        return new PrivateChannel(
            in_array($event, static::getGenericChannelEvents())
                ? Str::beforeLast($channel, '.') . '.'
                : $channel
        );
    }

    public function broadcastWith(): array
    {
        return static::getBroadcastOnlyKey() && method_exists($this, 'getKey')
            ? ['model' => [$this->getKeyName() => $this->getKey()]]
            : $this->baseBroadcastWith();
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
}
