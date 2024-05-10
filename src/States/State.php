<?php

namespace FluxErp\States;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Spatie\ModelStates\State as BaseState;
use Spatie\ModelStates\StateConfig;

abstract class State extends BaseState implements Arrayable
{
    protected static ?array $config = null;

    protected static ?string $color = null;

    public static function registerStateConfig(StateConfig $config, ?string $baseStateClass = null): void
    {
        if (! is_a($baseStateClass ?? $config->baseStateClass, static::class, true)) {
            throw new \InvalidArgumentException(
                "The state class `{$config->baseStateClass}` must be a subclass of `" . static::class . '`'
            );
        }

        static::$config[$baseStateClass ?? $config->baseStateClass] = $config;
    }

    public function toArray(): array|string
    {
        return $this->__toString();
    }

    public function badge(): string
    {
        return Blade::render(html_entity_decode('<x-badge :$label :$color />'), [
            'color' => $this->color(),
            'label' => __(Str::headline($this->__toString())),
        ]);
    }
}
