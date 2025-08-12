<?php

namespace FluxErp\Notifications\Ticket;

use FluxErp\Models\Ticket;
use FluxErp\Support\Notification\SubscribableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketCreatedNotification extends SubscribableNotification implements ShouldQueue
{
    use Queueable;

    public function subscribe(): array
    {
        return [
            'eloquent.created: ' . morph_alias(Ticket::class) => 'sendNotification',
        ];
    }

    protected function getDescription(): ?string
    {
        return $this->model->getLabel();
    }

    protected function getNotificationIcon(): ?string
    {
        return 'support';
    }

    protected function getTitle(): string
    {
        return __(
            ':username created a ticket',
            [
                'username' => $this->model->getCreatedBy()?->getLabel() ?? __('Unknown'),
            ],
        );
    }
}
