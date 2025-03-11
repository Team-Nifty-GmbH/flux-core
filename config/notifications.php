<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Model Notifications
    |--------------------------------------------------------------------------
    |
    | This array contains all the models that can trigger a specific notification.
    */

    'model_notifications' => [
        resolve_static(FluxErp\Models\Comment::class, 'class') => [
            'created' => FluxErp\Notifications\Comment\CommentCreatedNotification::class,
        ],
        resolve_static(FluxErp\Models\Ticket::class, 'class') => [
            'created' => FluxErp\Notifications\Ticket\TicketCreatedNotification::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Channels
    |--------------------------------------------------------------------------
    |
    | This array contains all the channels that can be used to send notifications.
    | The key is the name of the channel, inside the array you can specify:
    | driver: A class containing a send method.
    | method: A method that will be called on the notification class to create the Message.
    |
    */

    'channels' => [
        'mail' => [
            'driver' => Illuminate\Notifications\Channels\MailChannel::class,
            'method' => 'toMail',
        ],
        'database' => [
            'driver' => Illuminate\Notifications\Channels\DatabaseChannel::class,
        ],
        'broadcast' => [
            'driver' => Illuminate\Notifications\Channels\BroadcastChannel::class,
        ],
        'web_push' => [
            'driver' => NotificationChannels\WebPush\WebPushChannel::class,
            'method' => 'toWebPush',
        ],
    ],
];
