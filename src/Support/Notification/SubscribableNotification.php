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
use FluxErp\Traits\Model\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Str;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
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

    public function getRoute(): ?string
    {
        return $this->route ?? request()->header('referer');
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

    public function toFcm(object $notifiable): ?FcmNotification
    {
        return $this->toToastNotification($notifiable)->toFcm();
    }

    public function toFcmData(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toFcmData();
    }

    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof AnonymousNotifiable
            && ! in_array(resolve_static(get_class($notifiable), 'class'), static::sendsTo())
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
                method_exists($this->model, 'detailRoute')
                    ? $this->model->detailRoute()
                    : $this->getRoute()
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
        if (! method_exists($event, 'broadcastChannel')) {
            throw new Exception('Event must have a broadcast channel.');
        }

        $channel = $event->broadcastChannel();
        if ($event instanceof Model && $event->wasRecentlyCreated) {
            $channel = Str::chopEnd($channel, '.' . $event->getKey()) . '.';
        }

        return $channel;
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
