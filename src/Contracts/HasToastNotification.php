<?php

namespace FluxErp\Contracts;

use FluxErp\Support\Notification\ToastNotification\ToastNotification;

interface HasToastNotification
{
    public function toToastNotification(object $notifiable): ToastNotification;
}
