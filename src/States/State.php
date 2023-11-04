<?php

namespace FluxErp\States;

use FluxErp\Casts\TranslatableStateCaster;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Spatie\ModelStates\State as BaseState;

abstract class State extends BaseState implements Arrayable
{
    public function toArray(): array|string
    {
        return $this->__toString();
    }

    public static function getStateMapping(): Collection
    {
        $stateMapping = parent::getStateMapping();

        return $stateMapping->mapWithKeys(fn ($item, $key) => [__($key) => $item]);
    }

    public static function castUsing(array $arguments): TranslatableStateCaster
    {
        return new TranslatableStateCaster(static::class);
    }

    public function getValue(): string
    {
        return __(static::getMorphClass());
    }
}
