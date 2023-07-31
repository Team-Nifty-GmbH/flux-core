<?php

namespace FluxErp\Events;

use FluxErp\Actions\FluxAction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class BroadcastActionExecuted implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    protected array $channels = [];

    public ?string $connection;

    public ?string $queue;

    public function __construct(public FluxAction $action)
    {
    }

    public function broadcastOn(): array
    {
        $channels = empty($this->channels)
            ? ($this->action->broadcastOn() ?: [])
            : $this->channels;

        return collect($channels)->map(function ($channel) {
            return $channel instanceof FluxAction ?
                new PrivateChannel(str_replace('\\', '.', $channel::class)) : $channel;
        })->all();
    }

    public function broadcastAs(): string
    {
        $default = class_basename($this->action) . 'Executed';

        return method_exists($this->action, 'broadcastAs')
            ? ($this->action->broadcastAs() ?: $default)
            : $default;
    }

    public function broadcastWith(): ?array
    {
        return method_exists($this->action, 'broadcastWith')
            ? $this->action->broadcastWith()
            : null;
    }

    public function onChannels(array $channels): static
    {
        $this->channels = $channels;

        return $this;
    }
}
