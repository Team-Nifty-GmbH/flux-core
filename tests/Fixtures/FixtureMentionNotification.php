<?php

namespace FluxErp\Tests\Fixtures;

use Illuminate\Notifications\Notification;

class FixtureMentionNotification extends Notification
{
    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
