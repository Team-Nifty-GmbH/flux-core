<?php

namespace FluxErp\Notifications\Ticket;

use FluxErp\Models\Ticket;
use FluxErp\Support\Notification\SubscribableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketUpdatedNotification extends SubscribableNotification implements ShouldQueue
{
    use Queueable;

    public function subscribe(): array
    {
        return [
            'eloquent.updated: ' . resolve_static(Ticket::class, 'class') => 'sendNotification',
        ];
    }

    protected function getTitle(): string
    {
        return __(
            ':username updated a ticket',
            [
                'username' => $this->model->getUpdatedBy()?->getLabel() ?? __('Unknown'),
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
}
