<?php

namespace FluxErp\Support\Metrics\Results;

class ValueResult
{
    public function __construct(
        protected float|string $value,
        protected float|string|null $previousValue,
        protected float|string|null $growthRate = null
    ) {}

    public static function make(
        float|string $value,
        float|string|null $previousValue,
        float|string|null $growthRate
    ): static {
        return app(static::class, [
            'value' => $value,
            'previousValue' => $previousValue,
            'growthRate' => $growthRate,
        ]);
    }

    public function getValue(): float|string
    {
        return $this->value;
    }

    public function getPreviousValue(): float|string|null
    {
        return $this->previousValue;
    }

    public function getGrowthRate(): float|string|null
    {
        return $this->growthRate;
    }
}
