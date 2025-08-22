<?php

namespace FluxErp\Notifications\Order;

use FluxErp\Events\Order\OrderApprovalRequestEvent;
use FluxErp\Support\Notification\SubscribableNotification;
use Illuminate\Database\Eloquent\Model;

class OrderApprovalRequestNotification extends SubscribableNotification
{
    public function subscribe(): array
    {
        return [
            resolve_static(OrderApprovalRequestEvent::class, 'class') => 'sendNotification',
        ];
    }

    protected function getDescription(): ?string
    {
        return $this->model->getLabel();
    }

    protected function getModelFromEvent(object $event): ?Model
    {
        return $event->order;
    }

    protected function getTitle(): string
    {
        return __(
            ':username requested approval for an order',
            [
                'username' => auth()->user()?->getLabel() ?? __('Unknown'),
            ],
        );
    }
}
