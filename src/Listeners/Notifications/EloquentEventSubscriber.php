<?php

namespace FluxErp\Listeners\Notifications;

use FluxErp\Actions\EventSubscription\CreateEventSubscription;
use FluxErp\Contracts\IsSubscribable;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

class EloquentEventSubscriber
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
            ! $model instanceof IsSubscribable
            || ! auth()->check()
            || ! method_exists(auth()->user(), 'eventSubscriptions')
        ) {
            return;
        }

        if (auth()
            ->user()
            ->eventSubscriptions()
            ->where('channel', $model->broadcastChannel())
            ->where('event', $event)
            ->where('is_notifiable', true)
            ->doesntExist()
        ) {
            try {
                CreateEventSubscription::make([
                    'channel' => $model->broadcastChannel(),
                    'event' => $event,
                    'subscribable_id' => auth()->id(),
                    'subscribable_type' => auth()->user()->getMorphClass(),
                    'is_broadcast' => false,
                    'is_notifiable' => true,
                ])
                    ->validate()
                    ->execute();
            } catch (UnauthorizedException|ValidationException) {
            }
        }
    }
}
