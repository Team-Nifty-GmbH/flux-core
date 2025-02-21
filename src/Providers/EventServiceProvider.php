<?php

namespace FluxErp\Providers;

use FluxErp\Listeners\Auth\LoginListener;
use FluxErp\Listeners\Auth\LogoutListener;
use FluxErp\Listeners\BroadcastEventSubscriber;
use FluxErp\Listeners\CacheKeyWrittenListener;
use FluxErp\Listeners\MailMessage\CreateMailExecutedSubscriber;
use FluxErp\Listeners\MessageSendingEventSubscriber;
use FluxErp\Listeners\Notifications\NotificationEloquentEventSubscriber;
use FluxErp\Listeners\Order\OrderInvoiceAddedSubscriber;
use FluxErp\Listeners\Order\OrderStockSubscriber;
use FluxErp\Listeners\SnapshotEventSubscriber;
use FluxErp\Listeners\Ticket\CommentCreatedListener;
use FluxErp\Listeners\Ticket\TicketCreatedNotificationListener;
use FluxErp\Listeners\WebhookEventSubscriber;
use FluxErp\Models\Comment;
use FluxErp\Notifications\Comment\CommentCreatedNotification;
use FluxErp\Notifications\Order\DocumentSignedNotification;
use FluxErp\Notifications\Order\OrderApprovalRequestNotification;
use FluxErp\Notifications\Task\TaskAssignedNotification;
use FluxErp\Notifications\Task\TaskUpdatedNotification;
use FluxErp\Notifications\Ticket\TicketAssignedNotification;
use FluxErp\Notifications\Ticket\TicketCreatedNotification;
use FluxErp\Notifications\Ticket\TicketUpdatedNotification;
use FluxErp\Support\QueueMonitor\QueueMonitorManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
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
        OrderStockSubscriber::class,
        OrderInvoiceAddedSubscriber::class,
        MessageSendingEventSubscriber::class,
        CreateMailExecutedSubscriber::class,
        CommentCreatedNotification::class,
        DocumentSignedNotification::class,
        TaskAssignedNotification::class,
        TaskUpdatedNotification::class,
        OrderApprovalRequestNotification::class,
        TicketAssignedNotification::class,
        TicketUpdatedNotification::class,
    ];

    public function boot(): void
    {
        Event::listen(JobQueued::class, function (JobQueued $event) {
            QueueMonitorManager::handle($event);
        });

        /** @var QueueManager $manager */
        $manager = app(QueueManager::class);

        $manager->before(static function (JobProcessing $event) {
            QueueMonitorManager::handle($event);
        });

        $manager->after(static function (JobProcessed $event) {
            QueueMonitorManager::handle($event);
        });

        $manager->failing(static function (JobFailed $event) {
            QueueMonitorManager::handle($event);
        });

        $manager->exceptionOccurred(static function (JobExceptionOccurred $event) {
            QueueMonitorManager::handle($event);
        });
    }
}
