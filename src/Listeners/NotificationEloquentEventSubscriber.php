<?php

namespace FluxErp\Listeners;

use FluxErp\Models\NotificationSetting;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class NotificationEloquentEventSubscriber
{
    public Collection $notifiables;

    public Model $model;

    /**
     * Handle incoming events.
     */
    public function sendNotification($event, $model): void
    {
        $eventType = explode('.', explode(':', $event)[0])[1];
        $notification = config('notifications.model_notifications.' . get_class($model[0]) . '.' . $eventType);

        if (! $notification) {
            return;
        }

        $this->model = $model[0];

        // Subscribers to the morph model.
        $this->notifiables = event_subscribers($event, $model[0]->model_id, $model[0]->model_type);
        // Subscribers to this model
        $this->notifiables = $this->notifiables->merge(event_subscribers($event, $model[0]->id, get_class($model[0])));

        if (method_exists($model[0], 'notifiable')) {
            // Subscribers to the notifiable model.
            $this->notifiables = $this->notifiables->merge($model[0]->notifiable()->get());
        }

        // Anonymous subscribers.
        $anonymousNotifiables = resolve_static(NotificationSetting::class, 'query')
            ->where('notification_type', $notification)
            ->where('is_active', true)
            ->whereNull('notifiable_id')
            ->whereNull('notifiable_type')
            ->get()
            ->map(function ($setting) {
                $driver = array_keys(
                    collect(config('notifications.channels'))
                        ->where('driver', $setting->channel)
                        ->toArray()
                )[0] ?? null;

                return $driver
                    ? Notification::route(
                        $driver,
                        $setting->channel_value
                    )
                    : null;
            })
            ->filter();

        $this->notifiables = $this->notifiables->merge($anonymousNotifiables);

        app()->make(Dispatcher::class)->dispatch($notification, $this);

        if (! $this->notifiables->count()) {
            return;
        }

        Notification::send($this->notifiables, new $notification($this->model, $event));
    }

    /**
     * Register the listeners for the subscriber.
     * E.g. CommentCreatedNotification::class => 'sendNotification'
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events): array
    {
        return [
            'eloquent.retrieved: *' => 'sendNotification',
            'eloquent.creating: *' => 'sendNotification',
            'eloquent.created: *' => 'sendNotification',
            'eloquent.updating: *' => 'sendNotification',
            'eloquent.updated: *' => 'sendNotification',
            'eloquent.saving: *' => 'sendNotification',
            'eloquent.saved: *' => 'sendNotification',
            'eloquent.deleting: *' => 'sendNotification',
            'eloquent.deleted: *' => 'sendNotification',
            'eloquent.trashed: *' => 'sendNotification',
            'eloquent.forceDeleted: *' => 'sendNotification',
            'eloquent.restoring: *' => 'sendNotification',
            'eloquent.restored: *' => 'sendNotification',
        ];
    }
}
