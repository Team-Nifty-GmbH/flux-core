<?php

namespace FluxErp\Support\Notification;

use Illuminate\Notifications\Notification;
use Ramsey\Uuid\Uuid;

class NotificationId
{
    /**
     * The queue monitor notifications derive their id from the job or the batch, so that a repeated
     * run addresses the toast it already opened. That id is the same for every recipient, while the
     * notifications table keys a row by id alone, so a second recipient would collide with the first.
     * Folding the notifiable into the stored id keeps one row per recipient and still resolves to the
     * same row whenever that recipient sees the job finish again.
     */
    public static function for(Notification $notification, object $notifiable): string
    {
        return Uuid::uuid5(
            Uuid::NAMESPACE_OID,
            implode('|', [
                $notification->id,
                morph_alias($notifiable::class),
                $notifiable->getKey(),
            ])
        )
            ->toString();
    }
}
