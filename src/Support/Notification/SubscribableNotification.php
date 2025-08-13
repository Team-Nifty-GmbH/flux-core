<?php

namespace FluxErp\Support\Notification;

use Exception;
use FluxErp\Contracts\HasToastNotification;
use FluxErp\Models\EventSubscription;
use FluxErp\Models\NotificationSetting;
use FluxErp\Models\User;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use FluxErp\Traits\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use NotificationChannels\WebPush\WebPushMessage;

abstract class SubscribableNotification extends Notification implements HasToastNotification
{
    public object $event;

    public ?Model $model;

    public ?string $route = null;

    abstract protected function getTitle(): string;

    abstract public function subscribe(): array;

    public function __construct()
    {
        $this->route = request()->header('referer');
    }

    public static function sendsTo(): array
    {
        return [
            resolve_static(User::class, 'class'),
        ];
    }

    public function sendNotification(object $event): void
    {
        $this->event = $event;
        $this->model = $this->getModelFromEvent($this->event);

        $this->getSubscriptionsForEvent($this->event)
            ->each(function (object $notifiable): void {
                $notifiable->notify($this);
            });

        if ($anonymousSubscriptions = $this->getAnonymousSubscriptions()) {
            NotificationFacade::routes($anonymousSubscriptions)
                ->notify($this);
        }
    }

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->toToastNotification($notifiable)->toMail();
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->notifiable($notifiable)
            ->title($this->getTitle())
            ->image($this->getNotificationIcon())
            ->when(
                auth()->user()
                && method_exists(auth()->user(), 'getAvatarUrl')
                && auth()->user()->getAvatarUrl(),
                fn (ToastNotification $toast) => $toast->image(auth()->user()->getAvatarUrl())
            )
            ->description($this->getDescription())
            ->accept($this->getAcceptAction($notifiable));
    }

    public function toWebPush(object $notifiable): ?WebPushMessage
    {
        if (! method_exists($notifiable, 'pushSubscriptions')
            || ! $notifiable->pushSubscriptions()->exists()
        ) {
            return null;
        }

        return $this->toToastNotification($notifiable)->toWebPush();
    }

    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof AnonymousNotifiable
            && ! in_array(get_class($notifiable), static::sendsTo())
        ) {
            return [];
        }

        return parent::via($notifiable);
    }

    protected function getAcceptAction(object $notifiable): NotificationAction
    {
        return NotificationAction::make()
            ->label(__('View'))
            ->url(
                ! $notifiable instanceof User
                && method_exists($this->model, 'getPortalDetailRoute')
                    ? $this->model->getPortalDetailRoute()
                    : (
                        $this->route
                        ?? (
                            method_exists($this->model, 'detailRoute')
                                ? $this->model->detailRoute()
                                : null
                        )
                    )
            );
    }

    protected function getAnonymousSubscriptions(): array
    {
        return resolve_static(NotificationSetting::class, 'query')
            ->whereNull('notifiable_type')
            ->whereNull('notifiable_id')
            ->where('notification_type', static::class)
            ->where('is_active', true)
            ->pluck('channel_value', 'channel')
            ->filter()
            ->toArray();
    }

    protected function getChannelFromEvent(object $event): string
    {
        return method_exists($event, 'broadcastChannel')
            ? $event->broadcastChannel()
            : throw new Exception('Event must have a broadcast channel.');
    }

    protected function getDescription(): ?string
    {
        return null;
    }

    protected function getEventNames(): array
    {
        return array_merge(
            array_keys($this->subscribe()),
            ['*']
        );
    }

    protected function getModelFromEvent(object $event): ?Model
    {
        return $event instanceof Model
            ? $event
            : null;
    }

    protected function getNotificationIcon(): ?string
    {
        return null;
    }

    protected function getSubscriptionsForEvent(object $event): Collection
    {
        return resolve_static(EventSubscription::class, 'query')
            ->with('subscribable')
            ->where(function (Builder $query): void {
                $query->whereNot('subscribable_type', auth()->user()?->getMorphClass())
                    ->orWhere('subscribable_id', '!=', auth()->id());
            })
            ->where('channel', $this->getChannelFromEvent($event))
            ->whereIn('event', $this->getEventNames())
            ->whereHas('subscribable', fn (Builder $query) => $query->where('is_active', true))
            ->get()
            ->map(fn (EventSubscription $subscription) => $subscription->subscribable)
            ->unique()
            ->filter(fn ($notifiable) => in_array(Notifiable::class, class_uses_recursive($notifiable)));
    }
}
