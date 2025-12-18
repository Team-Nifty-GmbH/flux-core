<?php

namespace FluxErp\Support\Event;

use BadMethodCallException;
use FluxErp\Models\User;
use FluxErp\Traits\Makeable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

abstract class SubscribableEvent implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, Makeable;

    protected ?Collection $subscribers = null;

    protected ?Collection $unsubscribers = null;

    public function broadcastChannel(): ?string
    {
        return collect(get_object_vars($this))
            ->first(fn ($property) => is_object($property) && method_exists($property, 'broadcastChannel'))
            ?->broadcastChannel();
    }

    public function eventName(): string
    {
        return static::class;
    }

    public function getSubscribers(): ?Collection
    {
        return $this->subscribers;
    }

    public function getUnsubscribers(): ?Collection
    {
        return $this->unsubscribers;
    }

    public function subscribeChannel(array|Arrayable $subscribers, string $type = User::class): static
    {
        $this->subscribers ??= collect();

        collect($subscribers)
            ->map(fn ($subscriber) => $subscriber instanceof Model
                ? $subscriber
                : resolve_static($type, 'query')
                    ->whereKey($subscriber)
                    ->first()
            )
            ->filter()
            ->each(function (Model $subscriber): void {
                try {
                    $subscriber->subscribeNotificationChannel($this->broadcastChannel(), $this->eventName());
                    $this->subscribers->push($subscriber);
                } catch (BadMethodCallException|UnauthorizedException|ValidationException) {
                }
            });

        return $this;
    }

    public function unsubscribeChannel(array|Arrayable $subscribers, string $type = User::class): static
    {
        $this->unsubscribers ??= collect();

        collect($subscribers)
            ->map(fn ($subscriber) => $subscriber instanceof Model
                ? $subscriber
                : resolve_static($type, 'query')
                    ->whereKey($subscriber)
                    ->first()
            )
            ->filter()
            ->each(function (Model $subscriber): void {
                try {
                    $subscriber->unsubscribeNotificationChannel($this->broadcastChannel(), $this->eventName());
                    $this->unsubscribers->push($subscriber);
                } catch (BadMethodCallException|UnauthorizedException|ValidationException) {
                }
            });

        return $this;
    }
}
