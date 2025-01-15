<?php

namespace FluxErp\Support\Event;

use FluxErp\Models\User;
use FluxErp\Traits\Makeable;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

class SubscribableEvent implements ShouldDispatchAfterCommit
{
    use Makeable;

    protected ?Collection $subscribers = null;

    protected ?Collection $unsubscribers = null;

    public function broadcastChannel(): ?string
    {
        return collect(get_object_vars($this))
            ->first(fn ($property) => is_object($property) && method_exists($property, 'broadcastChannel'))
            ?->broadcastChannel();
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
            ->each(function (Model $subscriber) {
                try {
                    $subscriber->subscribeNotificationChannel($this->broadcastChannel());
                    $this->subscribers->push($subscriber);
                } catch (UnauthorizedException|ValidationException) {
                }
            });

        return $this;
    }

    public function unsubscribeChannel(array|Arrayable $subscribers, string $type = User::class): static
    {
        collect($subscribers)
            ->map(fn ($subscriber) => $subscriber instanceof Model
                ? $subscriber
                : resolve_static($type, 'query')
                    ->whereKey($subscriber)
                    ->first()
            )
            ->filter()
            ->each(function (Model $subscriber) {
                try {
                    $subscriber->unsubscribeNotificationChannel($this->broadcastChannel());
                    $this->subscribers->push($subscriber);
                } catch (UnauthorizedException|ValidationException) {
                }
            });

        return $this;
    }

    public function getUnsubscribers(): ?Collection
    {
        return $this->unsubscribers;
    }

    public function getSubscribers(): ?Collection
    {
        return $this->subscribers;
    }
}
