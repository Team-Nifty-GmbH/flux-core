<?php

namespace FluxErp\Support\Notification;

use FluxErp\Events\BroadcastNowNotificationCreated;
use Illuminate\Notifications\Channels\BroadcastChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class BroadcastNowChannel extends BroadcastChannel
{
    public function send($notifiable, Notification $notification): ?array
    {
        $message = $this->getData($notifiable, $notification);

        // The payload keeps the job derived id as its contextId, which is what addresses the toast.
        // What travels as the id has to be the row the database channel writes, because that is what
        // the client looks the notification up by before it marks it as read.
        $notification->id = NotificationId::for($notification, $notifiable);

        $event = new BroadcastNowNotificationCreated(
            $notifiable, $notification, is_array($message) ? $message : $message->data
        );

        if ($message instanceof BroadcastMessage) {
            $event->onConnection($message->connection)
                ->onQueue($message->queue);
        }

        return $this->events->dispatch($event);
    }
}
