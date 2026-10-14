<?php

namespace FluxErp\Notifications;

use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use FluxErp\Traits\Makeable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewActionPermissionsNotification extends Notification implements ShouldQueue
{
    use Makeable, Queueable;

    public function __construct(protected array $permissions) {}

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->notifiable($notifiable)
            ->title(__(':count new action permissions', ['count' => count($this->permissions)]))
            ->description(
                __('No role holds these yet, grant them to the roles that need them:')
                . ' ' . implode(', ', $this->permissions)
            )
            ->accept(
                NotificationAction::make()
                    ->label(__('Permissions'))
                    ->url(route('settings.permissions'))
            );
    }
}
