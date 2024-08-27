<?php

namespace FluxErp\Support\Metrics\Results;

use ArrayAccess;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;

class ValueResult implements ArrayAccess, Responsable
{
    public array $container;

    public function __construct(
        protected float $value,
        protected ?float $previousValue,
        protected ?float $growthRate = null
    ) {
        $this->container = [$value, $previousValue, $growthRate];
    }

    public static function make(float $value, ?float $previousValue, ?float $growthRate): static
    {
        return app(static::class, [
            'value' => $value,
            'previousValue' => $previousValue,
            'growthRate' => $growthRate,
        ]);
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getPreviousValue(): ?float
    {
        return $this->previousValue;
    }

    public function getGrowthRate(): ?float
    {
        return $this->growthRate;
    }

    public function toResponse($request): Response
    {
        return new Response(
            $this->getValue()
        );
    }

    public function offsetSet($offset, $value): void
    {
        throw new \Exception('ValueResult is immutable');
    }

    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset): void
    {
        throw new \Exception('ValueResult is immutable');
    }

    public function offsetGet($offset): mixed
    {
        return $this->container[$offset] ?? null;
    }
}
