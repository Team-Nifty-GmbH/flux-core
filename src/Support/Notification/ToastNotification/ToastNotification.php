<?php

namespace FluxErp\Support\Notification\ToastNotification;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use NotificationChannels\WebPush\WebPushMessage;

class ToastNotification implements Arrayable
{
    protected ?string $title = null;

    protected ?string $description = null;

    protected ?string $icon = null;

    protected ?string $iconColor = null;

    protected ?string $img = null;

    protected bool $closeButton = true;

    protected ?int $timeout = 8500;

    protected ?bool $dense = null;

    protected ?bool $rightButtons = true;

    protected ?bool $progressbar = null;

    protected mixed $params = null;

    protected ?string $method = null;

    protected ?string $emit = null;

    protected ?string $to = null;

    protected ?NotificationAction $accept = null;

    protected ?NotificationAction $reject = null;

    protected ?string $acceptLabel = null;

    protected ?string $rejectLabel = null;

    protected ?NotificationEvent $onClose = null;

    protected ?NotificationEvent $onDismiss = null;

    protected ?NotificationEvent $onTimeout = null;

    protected array $attributes = [];

    protected ?object $notifiable = null;

    public static function make(...$arguments): static
    {
        $instance = app(static::class);

        if (count($arguments) === 1 && array_is_list($arguments)) {
            $arguments = Arr::wrap($arguments[0]);
        }

        foreach ($arguments as $key => $value) {
            if (method_exists($instance, $key)) {
                $instance->$key($value);
            }
        }

        return $instance;
    }

    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            $this->attributes[$name] = $value;
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return data_get($this->attributes, $name);
    }

    public function notifiable(object $notifiable): static
    {
        $this->notifiable = $notifiable;

        return $this;
    }

    public function when(\Closure|bool $condition, \Closure $callback): static
    {
        if ($condition instanceof \Closure) {
            $condition = $condition();
        }

        if ($condition) {
            return $callback($this);
        }

        return $this;
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function description(?string $description = null): static
    {
        $this->description = $description;

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function iconColor(string $iconColor): static
    {
        $this->iconColor = $iconColor;

        return $this;
    }

    public function img(string $img): static
    {
        $this->img = $img;

        return $this;
    }

    public function closeButton(bool $closeButton): static
    {
        $this->closeButton = $closeButton;

        return $this;
    }

    public function timeout(int $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function dense(bool $dense): static
    {
        $this->dense = $dense;

        return $this;
    }

    public function rightButtons(bool $rightButtons): static
    {
        $this->rightButtons = $rightButtons;

        return $this;
    }

    public function progressbar(bool $progressbar): static
    {
        $this->progressbar = $progressbar;

        return $this;
    }

    public function params(mixed $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function method(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function emit(string $emit): static
    {
        $this->emit = $emit;

        return $this;
    }

    public function to(string $to): static
    {
        $this->to = $to;

        return $this;
    }

    public function accept(?NotificationAction $accept = null): static
    {
        $this->accept = $accept;

        return $this;
    }

    public function reject(?NotificationAction $reject = null): static
    {
        $this->reject = $reject;

        return $this;
    }

    public function acceptLabel(string $acceptLabel): static
    {
        $this->acceptLabel = $acceptLabel;

        return $this;
    }

    public function rejectLabel(string $rejectLabel): static
    {
        $this->rejectLabel = $rejectLabel;

        return $this;
    }

    public function onClose(NotificationEvent $onClose): static
    {
        $this->onClose = $onClose;

        return $this;
    }

    public function onDismiss(NotificationEvent $onDismiss): static
    {
        $this->onDismiss = $onDismiss;

        return $this;
    }

    public function onTimeout(NotificationEvent $onTimeout): static
    {
        $this->onTimeout = $onTimeout;

        return $this;
    }

    public function href(string $url, string $label = 'Open…'): static
    {
        $this->accept(NotificationAction::make()->label(__($label))->url($url));

        return $this;
    }

    public function attributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(
            [
                'title' => $this->title,
                'description' => $this->description,
                'icon' => $this->icon,
                'iconColor' => $this->iconColor,
                'img' => $this->img,
                'closeButton' => $this->closeButton,
                'timeout' => $this->timeout,
                'dense' => $this->dense,
                'rightButtons' => $this->rightButtons,
                'progressbar' => $this->progressbar,
                'params' => $this->params,
                'method' => $this->method,
                'emit' => $this->emit,
                'to' => $this->to,
                'accept' => $this->accept?->toArray(),
                'reject' => $this->reject?->toArray(),
                'acceptLabel' => $this->acceptLabel,
                'rejectLabel' => $this->rejectLabel ?? __('Cancel'),
                'onClose' => $this->onClose?->toArray(),
                'onDismiss' => $this->onDismiss?->toArray(),
                'onTimeout' => $this->onTimeout?->toArray(),
            ],
            $this->attributes
        );
    }

    public function toMail(): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->greeting(__('Hello') . ' ' . $this->notifiable?->name)
            ->subject($this->title ?? '')
            ->line(new HtmlString($this->description ?? ''));

        if ($this->accept || $this->acceptLabel) {
            $mailMessage->action($this->acceptLabel ?? $this->accept?->label ?? '', $this->accept?->url ?? '');
        }

        return $mailMessage;
    }

    public function toWebPush(): ?WebPushMessage
    {
        if (! $this->notifiable
            || ! method_exists($this->notifiable, 'pushSubscriptions')
            || ! $this->notifiable->pushSubscriptions()->exists()
        ) {
            return null;
        }

        return (new WebPushMessage)
            ->icon($this->img)
            ->title($this->title)
            ->body($this->description)
            ->data(['url' => $this->accept?->url ?? '']);
    }
}
