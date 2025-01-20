<?php

namespace FluxErp\Traits;

use FluxErp\Actions\EventSubscription\CreateEventSubscription;
use FluxErp\Actions\EventSubscription\DeleteEventSubscription;
use FluxErp\Models\EventSubscription;
use FluxErp\Models\NotificationSetting;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notifiable as BaseNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

trait Notifiable
{
    use BaseNotifiable {
        BaseNotifiable::routeNotificationFor as protected baseRouteNotificationFor;
    }

    public function eventSubscriptions(): MorphMany
    {
        return $this->morphMany(EventSubscription::class, 'subscribable');
    }

    public function notificationSettings(): MorphMany
    {
        return $this->morphMany(NotificationSetting::class, 'notifiable');
    }

    public function notificationChannels(Notification $notification): array
    {
        $query = $this
            ->notificationSettings()
            ->select(['channel'])
            ->where('notification_type', get_class($notification));

        $activeChannels = array_column(
            $query->clone()
                ->where('is_active', true)
                ->get()
                ->toArray(),
            'channel'
        );

        $inactiveChannels = array_column(
            $query->where('is_active', false)
                ->get()
                ->toArray(),
            'channel'
        );

        $defaultChannels = array_diff($notification::defaultChannels($this), $inactiveChannels);

        return array_values(array_unique(array_merge($activeChannels, $defaultChannels)));
    }

    public function subscribeNotificationChannel(string $channel): ?EventSubscription
    {
        if ($this->eventSubscriptions()->where('channel', $channel)->exists()) {
            return null;
        }

        return CreateEventSubscription::make([
            'channel' => $channel,
            'subscribable_id' => $this->getKey(),
            'subscribable_type' => $this->getMorphClass(),
            'is_broadcast' => false,
            'is_notifiable' => true,
        ])
            ->validate()
            ->execute();
    }

    public function unsubscribeNotificationChannel(string $channel): bool
    {
        try {
            return DeleteEventSubscription::make([
                'id' => $this->eventSubscriptions()->where('channel', $channel)->value('id'),
            ])
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException) {
            return false;
        }
    }
}
