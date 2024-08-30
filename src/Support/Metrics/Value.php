<?php

namespace FluxErp\Support\Metrics;

use FluxErp\Support\Metrics\Results\ValueResult;
use Illuminate\Database\Eloquent\Builder;

class Value extends Metric
{
    public function min(string $column): string|ValueResult
    {
        return $this->setType('min', $column);
    }

    public function max(string $column): string|ValueResult
    {
        return $this->setType('max', $column);
    }

    public function sum(string $column): string|ValueResult
    {
        return $this->setType('sum', $column);
    }

    public function avg(string $column): string|ValueResult
    {
        return $this->setType('avg', $column);
    }

    public function count(string $column = '*'): string|ValueResult
    {
        return $this->setType('count', $column);
    }

    protected function setType(string $type, string $column): string|ValueResult
    {
        $this->type = $type;
        $this->column = $column;

        return $this->resolve();
    }

    protected function resolveValue(?array $range): string
    {
        $value = $this->query
            ->clone()
            ->withoutEagerLoads()
            ->when($range, fn (Builder $query) => $query
                ->whereBetween(...$this->resolveBetween($range))
            )
            ->{$this->type}($this->column);

        return $this->transformResult($value);
    }

    protected function resolvePreviousValue(): int|string
    {
        $range = $this->previousRange();

        if (! $range) {
            return 0;
        }

        return $this->resolveValue($range);
    }

    protected function resolveCurrentValue(): string
    {
        return $this->resolveValue(
            $this->currentRange()
        );
    }

    protected function resolve(): string|ValueResult
    {
        if (! $this->withGrowthRate) {
            return $this->resolveCurrentValue();
        }

        $currentValue = $this->resolveCurrentValue();
        $previousValue = $this->resolvePreviousValue();

        return ValueResult::make(
            $currentValue,
            $previousValue,
            $this->growthRateType->getValue($previousValue, $currentValue)
        );
    }
}
