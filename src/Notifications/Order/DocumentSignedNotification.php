<?php

namespace FluxErp\Notifications\Order;

use FluxErp\Events\Order\DocumentSignedEvent;
use FluxErp\Support\Notification\SubscribableNotification;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentSignedNotification extends SubscribableNotification implements ShouldQueue
{
    use Queueable;

    public function subscribe(): array
    {
        return [
            DocumentSignedEvent::class => 'sendNotification',
        ];
    }

    protected function getTitle(): string
    {
        return __(
            ':username signed your :model',
            [
                'username' => data_get($this->model, 'custom_properties.name', __('Unknown')),
                'model' => __('your ' . $this->model->model->getMorphClass()),
            ],
        );
    }

    protected function getModelFromEvent(object $event): ?Model
    {
        return $event->signature;
    }

    protected function getAcceptAction(object $notifiable): NotificationAction
    {
        return NotificationAction::make()
            ->label(__('View'))
            ->url($this->model->model->detailRoute());
    }

    protected function getDescription(): ?string
    {
        return __(Str::of($this->model->name)->after('signature-')->headline()->toString()) . ' - ' .
            $this->model->model->getLabel();
    }
}
