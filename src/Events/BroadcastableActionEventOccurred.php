<?php

namespace FluxErp\Events;

use FluxErp\Actions\FluxAction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Collection as BaseCollection;
use Throwable;

class BroadcastableActionEventOccurred implements ShouldBroadcast
{
    use InteractsWithSockets;

    public FluxAction $action;

    public string $connection;

    public string $queue;

    protected array $channels = [];

    protected string $event;

    public function __construct(FluxAction $action, string $event)
    {
        $action = clone $action;

        try {
            serialize($action->getResult());
        } catch (Throwable) {
            $action->setResult(null);
        }

        try {
            serialize($action->getData());
        } catch (Throwable) {
            $action->setData([]);
        }

        try {
            serialize($action->getRules());
        } catch (Throwable) {
            $action->setRules([]);
        }

        $this->action = $action;
        $this->event = $event;
    }

    public function broadcastAs(): string
    {
        $default = class_basename($this->action) . ucfirst($this->event);

        return method_exists($this->action, 'broadcastAs')
            ? ($this->action->broadcastAs($this->event) ?: $default)
            : $default;
    }

    public function broadcastOn(): array
    {
        $channels = ! $this->channels
            ? ($this->action->broadcastOn($this->event) ?: [])
            : $this->channels;

        return (new BaseCollection($channels))
            ->map(fn ($channel) => $channel instanceof FluxAction ? new PrivateChannel($channel) : $channel)
            ->all();
    }

    public function broadcastWith(): ?array
    {
        return method_exists($this->action, 'broadcastWith')
            ? $this->action->broadcastWith($this->event)
            : null;
    }

    public function event(): string
    {
        return $this->event;
    }

    public function onChannels(array $channels): static
    {
        $this->channels = $channels;

        return $this;
    }
}
