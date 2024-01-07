<?php

namespace FluxErp\Providers;

use FluxErp\Listeners\Auth\LoginListener;
use FluxErp\Listeners\Auth\LogoutListener;
use FluxErp\Listeners\BroadcastEventSubscriber;
use FluxErp\Listeners\CacheKeyWrittenListener;
use FluxErp\Listeners\NotificationEloquentEventSubscriber;
use FluxErp\Listeners\Order\OrderInvoiceAddedSubscriber;
use FluxErp\Listeners\SnapshotEventSubscriber;
use FluxErp\Listeners\Ticket\CommentCreatedListener;
use FluxErp\Listeners\Ticket\TicketCreatedNotificationListener;
use FluxErp\Listeners\WebhookEventSubscriber;
use FluxErp\Models\Comment;
use FluxErp\Notifications\Ticket\TicketCreatedNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Login::class => [
            LoginListener::class,
        ],
        Logout::class => [
            LogoutListener::class,
        ],
        'eloquent.created: ' . Comment::class => [
            CommentCreatedListener::class,
        ],
        KeyWritten::class => [
            CacheKeyWrittenListener::class,
        ],
        TicketCreatedNotification::class => [
            TicketCreatedNotificationListener::class,
        ],
    ];

    protected $subscribe = [
        BroadcastEventSubscriber::class,
        NotificationEloquentEventSubscriber::class,
        SnapshotEventSubscriber::class,
        WebhookEventSubscriber::class,
        OrderInvoiceAddedSubscriber::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
