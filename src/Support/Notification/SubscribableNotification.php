<?php

namespace FluxErp\Support\Notification;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Models\EventSubscription;
use FluxErp\Models\User;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use FluxErp\Traits\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushMessage;

abstract class SubscribableNotification extends Notification implements HasToastNotification
{
    public ?string $route = null;

    public object $event;

    public ?Model $model;

    public function __construct()
    {
        $this->route = request()->header('referer');
    }

    abstract public function subscribe(): array;

    abstract protected function getTitle(): string;

    public function sendNotification(object $event): void
    {
        $this->event = $event;
        $this->model = $this->getModelFromEvent($this->event);

        resolve_static(EventSubscription::class, 'query')
            ->with('subscribable')
            ->where(function (Builder $query) {
                $query->whereNot('subscribable_type', auth()->user()?->getMorphClass())
                    ->orWhere('subscribable_id', '!=', auth()->id());
            })
            ->where('channel', $this->getChannelFromEvent($event))
            ->whereHas('subscribable', fn (Builder $query) => $query->where('is_active', true))
            ->get()
            ->map(fn (EventSubscription $subscription) => $subscription->subscribable)
            ->unique()
            ->filter(fn ($notifiable) => in_array(Notifiable::class, class_uses_recursive($notifiable)))
            ->each(function (object $notifiable) {
                $notifiable->notify($this);
            });
    }

    public function toWebPush(object $notifiable): ?WebPushMessage
    {
        if (! method_exists($notifiable, 'pushSubscriptions') || ! $notifiable->pushSubscriptions()->exists()) {
            return null;
        }

        return $this->toToastNotification($notifiable)->toWebPush();
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
        $createdBy = $this->model && method_exists($this->model, 'getCreatedBy')
            ? $this->model->getCreatedBy()
            : null;

        return ToastNotification::make()
            ->notifiable($notifiable)
            ->title($this->getTitle())
            ->icon($this->getNotificationIcon())
            ->when(
                auth()->user()
                && method_exists(auth()->user(), 'getAvatarUrl')
                && auth()->user()->getAvatarUrl(),
                fn (ToastNotification $toast) => $toast->img(auth()->user()->getAvatarUrl())
            )
            ->description($this->getDescription())
            ->accept($this->getAcceptAction($notifiable));
    }

    protected function getNotificationIcon(): ?string
    {
        return null;
    }

    protected function getChannelFromEvent(object $event): string
    {
        return method_exists($event, 'broadcastChannel')
            ? $event->broadcastChannel()
            : throw new \Exception('Event must have a broadcast channel.');
    }

    protected function getModelFromEvent(object $event): ?Model
    {
        return $event instanceof Model
            ? $event
            : null;
    }

    protected function getDescription(): ?string
    {
        return null;
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
}
