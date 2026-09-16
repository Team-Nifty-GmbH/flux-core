<?php

namespace FluxErp\Contracts;

use Illuminate\Notifications\Notification;

interface ProvidesMentionNotification
{
    public function mentionNotification(): Notification;
}
