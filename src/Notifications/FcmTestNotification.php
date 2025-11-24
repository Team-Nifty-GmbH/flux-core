<?php

namespace FluxErp\Notifications;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Notifications\Channels\FcmChannel;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class FcmTestNotification extends Notification implements HasToastNotification
{
    public static function defaultChannels(?object $notifiable = null): array
    {
        return [
            FcmChannel::class,
        ];
    }

    public function toFcm(object $notifiable): ?FcmNotification
    {
        return $this->toToastNotification($notifiable)->toFcm();
    }

    public function toFcmData(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toFcmData();
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

    protected function getDescription(): ?string
    {
        return __('This is a test notification to verify that FCM push notifications are working correctly.');
    }

    protected function getTitle(): string
    {
        return __('FCM Push Notification Test');
    }
}
