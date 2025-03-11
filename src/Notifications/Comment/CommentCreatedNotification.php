<?php

namespace FluxErp\Notifications\Comment;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Models\Address;
use FluxErp\Models\Comment;
use FluxErp\Models\MailAccount;
use FluxErp\Models\User;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\WebPushMessage;

class CommentCreatedNotification extends Notification implements HasToastNotification, ShouldQueue
{
    use Queueable;

    public Comment $model;

    public ?string $route = null;

    public function __construct(Comment $model)
    {
        $this->model = $model;
        $this->route = request()->header('referer');
    }

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->toToastNotification($notifiable)
            ->toMail()
            ->when(
                $ticketAccount = resolve_static(MailAccount::class, 'query')
                    ->whereHas('mailFolders', fn ($query) => $query->where('can_create_ticket', true))
                    ->value('email'),
                fn (MailMessage $mail) => $mail->replyTo($ticketAccount)
            )
            ->line(new HtmlString(
                '<span style="display: none">[flux:comment:'
                . $this->model->model->getMorphClass() . ':'
                . $this->model->model->getKey()
                . ']</span>'
            )
            );
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        $createdBy = method_exists($this->model, 'getCreatedBy')
            ? $this->model->getCreatedBy()
            : null;

        return ToastNotification::make()
            ->notifiable($notifiable)
            ->title(
                Str::of(
                    __(
                        ':username commented on :model :label',
                        [
                            'username' => $createdBy && method_exists($createdBy, 'getLabel') && $createdBy->getLabel()
                                ? $createdBy->getLabel()
                                : __('Unknown'),
                            'model' => __('your ' . $this->model->model->getMorphClass()),
                            'label' => method_exists($this->model->model, 'getLabel') && $this->model->model->getLabel()
                                ? '"' . $this->model->model->getLabel() . '"'
                                : '',
                        ],
                    )
                )
                    ->trim()
                    ->deduplicate()
                    ->toString()
            )
            ->when(
                $createdBy
                && method_exists($createdBy, 'getAvatarUrl')
                && $createdBy->getAvatarUrl(),
                function (ToastNotification $toast) use ($createdBy) {
                    return $toast->image($createdBy->getAvatarUrl());
                }
            )
            ->description($this->model->comment)
            ->when(
                $this->model->model && method_exists($this->model->model, 'detailRoute'),
                function (ToastNotification $toast) use ($notifiable) {
                    return $toast->accept(
                        NotificationAction::make()
                            ->label(__('View'))
                            ->url(
                                $notifiable instanceof Address
                                    && method_exists($this->model->model, 'getPortalDetailRoute')
                                ? $this->model->model->getPortalDetailRoute()
                                : $this->route
                            )
                    );
                }
            );
    }

    public function toWebPush(object $notifiable): ?WebPushMessage
    {
        if (! method_exists($notifiable, 'pushSubscriptions') || ! $notifiable->pushSubscriptions()->exists()) {
            return null;
        }

        return $this->toToastNotification($notifiable)->toWebPush();
    }

    public function via(object $notifiable): array
    {
        if ($this->model->is_internal
            && ! is_a($notifiable, resolve_static(User::class, 'class'), true)
        ) {
            return [];
        }

        return parent::via($notifiable);
    }
}
