<?php

namespace FluxErp\Traits;

use FluxErp\Models\NotificationSetting;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notifiable as BaseNotifiable;
use Illuminate\Notifications\Notification;

trait Notifiable
{
    use BaseNotifiable {
        BaseNotifiable::routeNotificationFor as protected baseRouteNotificationFor;
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

        $defaultChannels = array_diff($notification::defaultChannels(), $inactiveChannels);

        return array_values(array_unique(array_merge($activeChannels, $defaultChannels)));
    }
}
