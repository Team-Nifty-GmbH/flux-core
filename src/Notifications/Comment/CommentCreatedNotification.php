<?php

namespace FluxErp\Notifications\Comment;

use FluxErp\Models\Address;
use FluxErp\Models\Comment;
use FluxErp\Models\MailAccount;
use FluxErp\Models\User;
use FluxErp\Support\Notification\SubscribableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CommentCreatedNotification extends SubscribableNotification implements ShouldQueue
{
    use Queueable;

    public static function sendsTo(): array
    {
        return array_merge(
            parent::sendsTo(),
            [
                resolve_static(Address::class, 'class'),
            ],
        );
    }

    public function subscribe(): array
    {
        return [
            'eloquent.created: ' . resolve_static(Comment::class, 'class') => 'sendNotification',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return parent::toMail($notifiable)
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
            ));
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

    protected function getDescription(): ?string
    {
        return $this->model->comment;
    }

    protected function getNotificationIcon(): ?string
    {
        return 'chat';
    }

    protected function getTitle(): string
    {
        return Str::of(
            __(
                ':username commented on :model :label',
                [
                    'username' => $this->model->getCreatedBy()?->getLabel() ?? __('Unknown'),
                    'model' => __('your ' . $this->model->model_type),
                    'label' => method_exists($this->model->model, 'getLabel') && $this->model->model->getLabel()
                        ? '"' . $this->model->model->getLabel() . '"'
                        : '',
                ],
            )
        )
            ->trim()
            ->deduplicate()
            ->toString();
    }
}
