<?php

namespace FluxErp\Support\Metrics\Results;

class ValueResult
{
    public function __construct(
        protected float|string|int $value,
        protected float|string|int|null $previousValue,
        protected float|string|int|null $growthRate = null
    ) {}

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
}
