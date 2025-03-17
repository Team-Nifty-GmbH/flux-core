<?php

namespace FluxErp\Support\Notification\ToastNotification;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class NotificationAction implements Arrayable
{
    protected ?string $execute = null;

    protected string $label;

    protected ?string $method = null;

    protected mixed $params = null;

    protected ?bool $solid = null;

    protected ?string $style = null;

    protected ?string $url = null;

    public function __construct()
    {
        $this->label = '';
    }

    public function __get($name)
    {
        return $this->$name;
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

    public function execute(string $execute): static
    {
        $this->execute = $execute;

        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;

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

    public function route(string $route, array $params = []): static
    {
        return $this->url(route($route, $params));
    }

    public function solid(bool $solid): static
    {
        $this->solid = $solid;

        return $this;
    }

    public function style(string $style): static
    {
        $this->style = $style;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'label' => $this->label,
            'style' => $this->style,
            'solid' => $this->solid,
            'url' => $this->url,
            'execute' => $this->execute,
            'method' => $this->method,
            'params' => $this->params,
        ]);
    }

    public function url(?string $url = null): static
    {
        $this->url = $url;

        return $this;
    }
}
