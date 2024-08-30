<?php

namespace FluxErp\Support\Metrics;

use FluxErp\Support\Metrics\Results\ValueResult;
use Illuminate\Database\Eloquent\Builder;

class Value extends Metric
{
    public function __call($method, $parameters)
    {
        if (in_array($method, ['min', 'max', 'sum', 'avg', 'count'], true)) {
            return $this->setType($method, $parameters[0] ?? '*');
        }

        return parent::__call($method, $parameters);
    }

    protected function setType(string $type, string $column): float|ValueResult
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

    public function resolvePreviousValue(): float
    {
        $range = $this->previousRange();

        if (! $range) {
            return 0;
        }

        return $this->resolveValue($range);
    }

    public function resolveCurrentValue(): float
    {
        return $this->resolveValue(
            $this->currentRange()
        );
    }

    protected function resolve(): float|ValueResult
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
