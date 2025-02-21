<?php

namespace FluxErp\Notifications\Ticket;

use FluxErp\Events\Ticket\TicketAssignedEvent;
use FluxErp\Support\Notification\SubscribableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TicketAssignedNotification extends SubscribableNotification implements ShouldQueue
{
    use Queueable;

    public function subscribe(): array
    {
        return [
            TicketAssignedEvent::class => 'sendNotification',
        ];
    }

    protected function getModelFromEvent(object $event): ?Model
    {
        return $event->ticket;
    }

    protected function getTitle(): string
    {
        return __(
            ':username assigned you a ticket',
            [
                'username' => auth()->user()?->getLabel() ?? __('Unknown'),
            ],
        );
    }

    protected function getDescription(): ?string
    {
        return $this->model->name;
    }

    protected function getNotificationIcon(): ?string
    {
        return 'support';
    }

    protected function getSubscriptionsForEvent(object $event): Collection
    {
        return parent::getSubscriptionsForEvent($event)
            ->intersect($event->getSubscribers());
    }
}
