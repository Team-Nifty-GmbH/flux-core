<?php

namespace FluxErp\Contracts;

use Kreait\Firebase\Messaging\Notification as FcmNotification;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Notifications that render through the ToastNotification pipeline and expose
 * a routable target. Implementations typically use the
 * `DelegatesToToastNotification` trait to satisfy the channel conversions.
 */
interface RoutableToastNotification extends HasToastNotification
{
    public function getRoute(): ?string;

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array;

    public function toWebPush(object $notifiable): ?WebPushMessage;

    public function toFcm(object $notifiable): ?FcmNotification;

    /**
     * @return array<string, mixed>
     */
    public function toFcmData(object $notifiable): array;
}
