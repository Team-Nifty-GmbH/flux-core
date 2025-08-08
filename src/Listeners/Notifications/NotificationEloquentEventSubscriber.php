<?php

namespace FluxErp\Listeners\Notifications;

use FluxErp\Actions\EventSubscription\CreateEventSubscription;
use FluxErp\Traits\HasNotificationSubscriptions;
use Illuminate\Validation\ValidationException;

class NotificationEloquentEventSubscriber
{
    public function subscribe($events): array
    {
        return [
            'eloquent.created: *' => 'subscribeNotifications',
            'eloquent.updated: *' => 'subscribeNotifications',
            'eloquent.restored: *' => 'subscribeNotifications',
            'eloquent.deleted: *' => 'subscribeNotifications',
        ];
    }

    public function subscribeNotifications($event, $model): void
    {
        $model = data_get($model, 0);
        if (
            ! in_array(HasNotificationSubscriptions::class, class_uses_recursive($model))
            || ! auth()->check()
            || ! method_exists(auth()->user(), 'eventSubscriptions')
        ) {
            return;
        }

        if (auth()->user()
            ->eventSubscriptions()
            ->where('channel', $model->broadcastChannel())
            ->doesntExist()
        ) {
            try {
                CreateEventSubscription::make([
                    'subscribable_id' => auth()->id(),
                    'subscribable_type' => auth()->user()->getMorphClass(),
                    'channel' => $model->broadcastChannel(),
                ])
                    ->validate()
                    ->execute();
            } catch (ValidationException) {
            }
        }
    }
}
