<?php

namespace FluxErp\Support\Notification\ToastNotification;

use FluxErp\Enums\ToastType;
use FluxErp\Support\TallstackUI\Interactions\Toast;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Traits\Macroable;
use NotificationChannels\WebPush\WebPushMessage;

class ToastNotification extends Toast implements Arrayable
{
    use Macroable;

    protected ?NotificationAction $accept = null;

    protected array $attributes = [];

    protected ?string $description = null;

    protected ?string $emit = null;

    protected int|string|null $id = null;

    protected ?string $method = null;

    protected ?object $notifiable = null;

    protected ?NotificationEvent $onClose = null;

    protected ?NotificationEvent $onDismiss = null;

    protected ?NotificationEvent $onTimeout = null;

    protected mixed $params = null;

    protected ?bool $progressbar = null;

    protected ?NotificationAction $reject = null;

    protected ?string $title = null;

    protected ?string $to = null;

    protected ToastType $toastType = ToastType::INFO;

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return data_get($this->attributes, $name);
    }

    public function __set($name, $value): void
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            $this->attributes[$name] = $value;
        }
    }

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

    public function accept(?NotificationAction $accept = null): static
    {
        $this->accept = $accept;

        return $this;
    }

    public function attributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function description(?string $description = null): static
    {
        $this->description = $description;

        return $this;
    }

    public function emit(string $emit): static
    {
        $this->emit = $emit;

        return $this;
    }

    public function href(string $url, string $label = 'Open…'): static
    {
        $this->accept(NotificationAction::make()->label(__($label))->url($url));

        return $this;
    }

    public function id(string|int|null $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function image(?string $image = null): static
    {
        $this->data['image'] = $image;

        return $this;
    }

    public function method(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function notifiable(object $notifiable): static
    {
        $this->notifiable = $notifiable;

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

    public function params(mixed $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function progressbar(bool $progressbar): static
    {
        $this->progressbar = $progressbar;

        return $this;
    }

    public function reject(?NotificationAction $reject = null): static
    {
        $this->reject = $reject;

        return $this;
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function to(string $to): static
    {
        $this->to = $to;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(
            [
                'title' => $this->title ?? '',
                'description' => $this->description,
                'toastType' => $this->toastType,
                'timeout' => $this->timeout,
                'progressbar' => $this->progressbar,
                'params' => $this->params,
                'method' => $this->method,
                'emit' => $this->emit,
                'to' => $this->to,
                'accept' => $this->accept?->toArray(),
                'reject' => $this->reject?->toArray(),
                'onClose' => $this->onClose?->toArray(),
                'onDismiss' => $this->onDismiss?->toArray(),
                'onTimeout' => $this->onTimeout?->toArray(),
            ],
            $this->attributes,
            $this->additional(),
            [
                'contextId' => $this->id,
            ]
        );
    }

    public function toMail(): MailMessage
    {
        $mailMessage = (new MailMessage())
            ->greeting(trim(
                __('Hello')
                . (property_exists($this->notifiable, 'name') ? ' ' . $this->notifiable->name : '')
            ))
            ->subject($this->title ?? '')
            ->line(new HtmlString($this->description));

        if ($this->accept) {
            $mailMessage->action($this->accept->label ?? '', $this->accept->url ?? '');
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

        return (new WebPushMessage())
            ->icon($this->img)
            ->title($this->title)
            ->body($this->description)
            ->data(['url' => $this->accept?->url ?? '']);
    }

    public function type(ToastType $type): static
    {
        $this->toastType = $type;

        return $this;
    }
}
