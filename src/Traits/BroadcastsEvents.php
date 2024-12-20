<?php

namespace FluxErp\Traits;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
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

    public static function getWithoutBroadcasting(): bool
    {
        return static::$withoutBroadcasting;
    }

    public static function getBroadcastOnlyKey(): bool
    {
        return static::$broadcastOnlyKey;
    }

    public function broadcastChannel(): string
    {
        return parent::broadcastChannel();
    }

    public function broadcastWith(): array
    {
        return static::getBroadcastOnlyKey() && method_exists($this, 'getKey')
            ? ['model' => [$this->getKeyName() => $this->getKey()]]
            : $this->baseBroadcastWith();
    }

    public function broadcastOn(string $event): array|Channel
    {
        $channel = $this->broadcastChannel();

        return new PrivateChannel($event === 'created' ? Str::beforeLast($channel, '.') . '.' : $channel);
    }
}
