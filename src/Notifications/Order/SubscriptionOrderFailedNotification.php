<?php

namespace FluxErp\Notifications\Order;

use FluxErp\Events\Order\SubscriptionOrderFailedEvent;
use FluxErp\Support\Notification\SubscribableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;

class SubscriptionOrderFailedNotification extends SubscribableNotification implements ShouldQueue
{
    use Queueable;

    public function subscribe(): array
    {
        return [
            resolve_static(SubscriptionOrderFailedEvent::class, 'class') => 'sendNotification',
        ];
    }

    protected function getDescription(): ?string
    {
        return $this->event->exceptionMessage;
    }

    protected function getModelFromEvent(object $event): ?Model
    {
        return $event->order;
    }

    protected function getNotificationIcon(): ?string
    {
        return 'exclamation-triangle';
    }

    protected function getTitle(): string
    {
        return __(
            'Subscription processing failed for :order_number',
            ['order_number' => data_get($this->model, 'order_number', '')],
        );
    }
}
