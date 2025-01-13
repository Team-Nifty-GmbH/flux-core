<?php

namespace FluxErp\Listeners\Notifications;

use FluxErp\Traits\HasNotificationSubscriptions;

class NotificationEloquentEventSubscriber
{
    public function subscribeNotifications($event, $model): void
    {
        $model = data_get($model, 0);
        if (! in_array(HasNotificationSubscriptions::class, class_uses_recursive($model))) {
            return;
        }

        if (auth()->user()
            ->eventSubscriptions()
            ->where('channel', $model->broadcastChannel())
            ->doesntExist()
        ) {
            auth()
                ->user()
                ->eventSubscriptions()
                ->create([
                    'channel' => $model->broadcastChannel(),
                ]);
        }
    }

    public function subscribe($events): array
    {
        return [
            'eloquent.created: *' => 'subscribeNotifications',
            'eloquent.updated: *' => 'subscribeNotifications',
            'eloquent.restored: *' => 'subscribeNotifications',
        ];
    }
}
