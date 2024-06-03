<?php

namespace FluxErp\Support\Notification\ToastNotification;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class NotificationEvent implements Arrayable
{
    protected ?string $url = null;

    protected ?string $method = null;

    protected mixed $params = null;

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

    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function method(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function params(mixed $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'method' => $this->method,
            'params' => $this->params,
        ];
    }
}
