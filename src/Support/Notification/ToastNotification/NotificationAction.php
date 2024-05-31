<?php

namespace FluxErp\Support\Notification\ToastNotification;

use Illuminate\Contracts\Support\Arrayable;

class NotificationAction implements Arrayable
{
    protected string $label;

    protected ?string $style = null;

    protected ?bool $solid = null;

    protected ?string $url = null;

    protected ?string $execute = null;

    protected ?string $method = null;

    protected mixed $params = null;

    public function __construct()
    {
        $this->label = '';
    }

    public static function make(...$arguments): static
    {
        $instance = app(static::class);

        if (count($arguments) === 1 && is_array($arguments[0])) {
            $arguments = $arguments[0];
        }

        foreach ($arguments as $key => $value) {
            $instance->$key = $value;
        }

        return $instance;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function style(string $style): static
    {
        $this->style = $style;

        return $this;
    }

    public function solid(bool $solid): static
    {
        $this->solid = $solid;

        return $this;
    }

    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function route(string $route, array $params = []): static
    {
        return $this->url(route($route, $params));
    }

    public function execute(string $execute): static
    {
        $this->execute = $execute;

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

    public function __get($name)
    {
        return $this->$name;
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
}
