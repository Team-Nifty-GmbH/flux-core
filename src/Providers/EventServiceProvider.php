<?php

namespace FluxErp\Providers;

use FluxErp\Listeners\Auth\LoginListener;
use FluxErp\Listeners\Auth\LogoutListener;
use FluxErp\Listeners\BroadcastEventSubscriber;
use FluxErp\Listeners\CacheKeyWrittenListener;
use FluxErp\Listeners\MailMessage\CreateMailExecutedSubscriber;
use FluxErp\Listeners\MessageSendingEventSubscriber;
use FluxErp\Listeners\Notifications\EloquentEventSubscriber;
use FluxErp\Listeners\Order\OrderInvoiceAddedSubscriber;
use FluxErp\Listeners\Order\OrderStockSubscriber;
use FluxErp\Listeners\RegisterMobilePushToken;
use FluxErp\Listeners\SnapshotEventSubscriber;
use FluxErp\Listeners\Ticket\CommentCreatedListener;
use FluxErp\Models\Comment;
use FluxErp\Models\Schedule;
use FluxErp\Notifications\Comment\CommentCreatedNotification;
use FluxErp\Notifications\Order\DocumentSignedNotification;
use FluxErp\Notifications\Order\OrderApprovalRequestNotification;
use FluxErp\Notifications\Task\TaskAssignedNotification;
use FluxErp\Notifications\Task\TaskCreatedNotification;
use FluxErp\Notifications\Task\TaskUpdatedNotification;
use FluxErp\Notifications\Ticket\TicketAssignedNotification;
use FluxErp\Notifications\Ticket\TicketCreatedNotification;
use FluxErp\Notifications\Ticket\TicketUpdatedNotification;
use FluxErp\Support\QueueMonitor\QueueMonitorManager;
use FluxErp\Traits\Job\TracksSchedule;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use TallStackUi\View\Components\Form\Date;
use Throwable;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LoginListener::class,
            RegisterMobilePushToken::class,
        ],
        Logout::class => [
            LogoutListener::class,
        ],
        KeyWritten::class => [
            CacheKeyWrittenListener::class,
        ],
    ];

    protected $subscribe = [
        BroadcastEventSubscriber::class,
        EloquentEventSubscriber::class,
        SnapshotEventSubscriber::class,
        OrderStockSubscriber::class,
        OrderInvoiceAddedSubscriber::class,
        MessageSendingEventSubscriber::class,
        CreateMailExecutedSubscriber::class,
        CommentCreatedNotification::class,
        DocumentSignedNotification::class,
        OrderApprovalRequestNotification::class,
        TaskAssignedNotification::class,
        TaskCreatedNotification::class,
        TaskUpdatedNotification::class,
        TicketAssignedNotification::class,
        TicketCreatedNotification::class,
        TicketUpdatedNotification::class,
    ];

    public function boot(): void
    {
        Event::listen(JobQueued::class, function (JobQueued $event): void {
            QueueMonitorManager::handle($event);
        });

        Event::listen(
            'eloquent.created: ' . resolve_static(Comment::class, 'class'),
            resolve_static(CommentCreatedListener::class, 'class')
        );

        /** @var QueueManager $manager */
        $manager = app(QueueManager::class);

        $manager->before(static function (JobProcessing $event): void {
            QueueMonitorManager::handle($event);
        });

        $manager->after(static function (JobProcessed $event): void {
            QueueMonitorManager::handle($event);

            $command = data_get($event->job->payload(), 'data.command');

            if (! is_string($command)) {
                return;
            }

            try {
                $job = unserialize($command);
            } catch (Throwable) {
                return;
            }

            if (! in_array(TracksSchedule::class, class_uses_recursive($job))
                || is_null($job->scheduleId)
            ) {
                return;
            }

            $schedule = resolve_static(Schedule::class, 'query')
                ->whereKey($job->scheduleId)
                ->first();

            if (! $schedule) {
                return;
            }

            if ($schedule->recurrences) {
                $schedule->current_recurrence++;
            }

            $schedule->last_success = now();
            $schedule->save();
        });

        $manager->failing(static function (JobFailed $event): void {
            QueueMonitorManager::handle($event);
        });

        $manager->exceptionOccurred(static function (JobExceptionOccurred $event): void {
            QueueMonitorManager::handle($event);
        });

        $this->app->resolving(Date::class, function (Date $component) {
            $component->start = Carbon::getWeekStartsAt();

            if ($format = data_get(Carbon::getTranslator()->getMessages(), 'de.formats.L')) {
                $component->format = $format;
            }

            return $component;
        });
    }
}
