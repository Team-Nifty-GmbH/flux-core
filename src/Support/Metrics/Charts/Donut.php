<?php

namespace FluxErp\Support\Metrics\Charts;

use BackedEnum;
use Exception;
use FluxErp\Support\Metrics\Metric;
use FluxErp\Support\Metrics\Results\Result;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use ReflectionEnum;
use ReflectionException;
use Spatie\ModelStates\State;
use UnitEnum;

class Donut extends Metric
{
    protected string $groupBy;

    protected ?string $labelKey = null;

    protected ?array $options = null;

    public function average(string $column, string $groupBy): Result
    {
        return $this->setType('avg', $column, $groupBy);
    }

    public function count(string $groupBy, string $column = '*'): Result
    {
        return $this->setType('count', $column, $groupBy);
    }

    public function getLabelKey(): ?string
    {
        return $this->labelKey;
    }

    /**
     * @throws ReflectionException
     */
    public function getOptions(): array
    {
        if ($this->options) {
            return $this->options;
        }

        $cast = $this->query->getModel()->getCasts()[$this->groupBy] ?? null;

        if ($cast && is_subclass_of($cast, UnitEnum::class) && (new ReflectionEnum($cast))->isBacked()) {
            return Arr::pluck($cast::cases(), 'value');
        }

        if ($cast && is_subclass_of($cast, State::class)) {
            return $cast::all()->keys()->toArray();
        }

        return [];
    }

    public function max(string $column, string $groupBy): Result
    {
        return $this->setType('max', $column, $groupBy);
    }

    public function min(string $column, string $groupBy): Result
    {
        return $this->setType('min', $column, $groupBy);
    }

    /**
     * @throws Exception
     */
    public function options(array|string $options): static
    {
        if (is_string($options)) {
            if (! enum_exists($options)) {
                throw new Exception("Enum $options does not exist");
            }

            $options = Arr::pluck($options::cases(), 'value');
        }

        $this->options = $options;

        return $this;
    }

    public function setLabelKey(string $labelKey): static
    {
        $this->labelKey = $labelKey;

        return $this;
    }

    public function sum(string $column, string $groupBy): Result
    {
        return $this->setType('sum', $column, $groupBy);
    }

    protected function resolve(): Result
    {
        if (! $this->withGrowthRate) {
            $data = $this->resolveCurrentValue();

            return Result::make(
                array_values($data),
                array_keys($data),
                null
            );
        }

        $previousData = $this->resolvePreviousValue();
        $currentData = $this->resolveCurrentValue();

        return Result::make(
            array_values($currentData),
            array_keys($currentData),
            $this->resolveGrowthRate($previousData, $currentData)
        );
    }

    protected function resolveCurrentValue(): array
    {
        return $this->resolveValue(
            $this->currentRange()
        );
    }

    protected function resolveGrowthRate(array $previousData, array $currentData): array
    {
        $growthRate = [];

        foreach ($currentData as $key => $currentValue) {
            $previousValue = $previousData[$key] ?? 0;

            $growthRate[$key] = $this->growthRateType->getValue($previousValue, $currentValue);
        }

        return $growthRate;
    }

    protected function resolvePreviousValue(): array
    {
        $range = $this->previousRange();

        if (! $range) {
            return [];
        }

        return $this->resolveValue($range);
    }

    protected function resolveValue(?array $range): array
    {
        $column = $this->query->getQuery()->getGrammar()->wrap($this->column);

        $results = $this->query
            ->clone()
            ->when($range, fn (Builder $query) => $query
                ->whereBetween(...$this->resolveBetween($range))
            )
            ->select([$this->groupBy, DB::raw("$this->type($column) as result")])
            ->groupBy($this->groupBy)
            ->get()
            ->mapWithKeys(function (Model $model) {
                $key = data_get($model, $this->labelKey ?? $this->groupBy);
                $key = $key instanceof BackedEnum ? $key->value : $key;

                return [
                    (string) $key => $this->transformResult($model->result),
                ];
            })
            ->toArray();

        $options = array_fill_keys($this->getOptions(), 0);
        $data = array_replace($options, $results);

        $cast = $this->query->getModel()->getCasts()[$this->groupBy] ?? null;

        if (
            $cast &&
            is_subclass_of($cast, UnitEnum::class) &&
            (new ReflectionEnum($cast))->isBacked() &&
            method_exists($cast, 'getLabel')
        ) {
            $data = Arr::mapWithKeys($data, fn (string $value, mixed $key) => [
                $cast::tryFrom($key)?->getLabel() => $value,
            ]);
        }

        if (
            $cast &&
            is_subclass_of($cast, State::class)
        ) {
            $data = Arr::mapWithKeys($data, fn (string $value, mixed $key) => [
                __($key) => $value,
            ]);
        }

        return $data;
    }

    protected function setType(string $type, string $column, string $groupBy): Result
    {
        $this->type = $type;
        $this->column = $column;
        $this->groupBy = $groupBy;

        return $this->resolve();
    }
}
