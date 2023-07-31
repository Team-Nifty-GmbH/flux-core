<?php

namespace FluxErp\Events;

use FluxErp\Actions\FluxAction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class BroadcastActionExecuted implements ShouldBroadcast
{
    use InteractsWithSockets;

    protected array $channels = [];

    public ?string $connection;

    public ?string $queue;

    private FluxAction $action;

    public function __construct(string $action, array $data, private readonly mixed $result = null)
    {
        $this->action = $action::make($data);
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
            : [
                'action' => get_class($this->action),
                'data' => $this->action->getData(),
                'result' => $this->result,
            ];
    }

    public function onChannels(array $channels): static
    {
        $this->channels = $channels;

        return $this;
    }
}
