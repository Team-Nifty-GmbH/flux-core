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
