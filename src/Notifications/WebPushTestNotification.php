<?php

namespace FluxErp\Notifications;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WebPushTestNotification extends Notification implements HasToastNotification
{
    public static function defaultChannels(?object $notifiable = null): array
    {
        return [
            WebPushChannel::class,
        ];
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->notifiable($notifiable)
            ->title($this->getTitle())
            ->when(
                auth()->user()
                && method_exists(auth()->user(), 'getAvatarUrl')
                && auth()->user()->getAvatarUrl(),
                fn (ToastNotification $toast) => $toast->image(auth()->user()->getAvatarUrl())
            )
            ->accept(
                NotificationAction::make()
                    ->label(__('View Dashboard'))
                    ->route('dashboard')
            )
            ->reject(
                NotificationAction::make()
                    ->label(__('View Profile'))
                    ->route('my-profile')
            )
            ->description($this->getDescription());
    }

    public function toWebPush(object $notifiable): ?WebPushMessage
    {
        if (! method_exists($notifiable, 'pushSubscriptions')
            || ! $notifiable->pushSubscriptions()->exists()
        ) {
            return null;
        }

        return $this->toToastNotification($notifiable)->toWebPush();
    }

    protected function getDescription(): ?string
    {
        return __('This is a test notification to verify that web push notifications are working correctly.');
    }

    protected function getTitle(): string
    {
        return __('Web Push Notification Test');
    }
}
