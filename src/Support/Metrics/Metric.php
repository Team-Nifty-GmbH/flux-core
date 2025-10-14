<?php

namespace FluxErp\Support\Metrics;

use Carbon\CarbonImmutable;
use FluxErp\Enums\GrowthRateTypeEnum;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Support\Calculation\Rounding;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;

abstract class Metric
{
    use Conditionable, Macroable;

    protected string $column;

    protected ?string $dateColumn = null;

    protected ?CarbonImmutable $endingDate = null;

    protected GrowthRateTypeEnum $growthRateType = GrowthRateTypeEnum::Percentage;

    protected Builder $query;

    protected ?string $range = null;

    protected array $ranges = [];

    protected ?CarbonImmutable $startingDate = null;

    protected string $type;

    protected bool $withGrowthRate = false;

    abstract protected function resolve(): mixed;

    public function __construct(Builder $query)
    {
        $this->query = $query->clone();

        $this->ranges = resolve_static(TimeFrameEnum::class, 'values');
    }

    public static function make(Builder $query): static
    {
        return app(static::class, ['query' => $query]);
    }

    public function currentRange(): ?array
    {
        $range = $this->getRange();

        if ($this->startingDate && $this->endingDate && $range === TimeFrameEnum::Custom) {
            return [
                $this->startingDate,
                $this->endingDate,
            ];
        }

        return resolve_static(TimeFrameEnum::class, 'getRange', ['case' => $range]);
    }

    public function getRange(): ?string
    {
        return $this->range ?? data_get($this->getRanges(), '0') ?? TimeFrameEnum::ThisWeek;
    }

    public function getRanges(): array
    {
        return $this->ranges;
    }

    public function modifyQuery(callable $callback): static
    {
        $callback($this->query);

        return $this;
    }

    public function previousRange(): ?array
    {
        $range = $this->getRange();

        if ($this->startingDate && $this->endingDate && $range === TimeFrameEnum::Custom) {
            $range = $this->startingDate->diffInDays($this->endingDate);

            return [
                $this->startingDate->subDays($range * 2),
                $this->endingDate->subDays($range),
            ];
        }

        return $range->getPreviousRange();
    }

    public function setDateColumn(string $dateColumn): static
    {
        $this->dateColumn = $dateColumn;

        return $this;
    }

    public function setEndingDate(string|CarbonImmutable|null $endingDate): static
    {
        $this->endingDate = is_string($endingDate) ? CarbonImmutable::parse($endingDate) : $endingDate;

        return $this;
    }

    public function setGrowthRateType(GrowthRateTypeEnum $growthRateType): static
    {
        $this->growthRateType = $growthRateType;

        return $this;
    }

    public function setRange(string $range): static
    {
        if (in_array($range, $this->getRanges())) {
            $this->range = $range;
        }

        return $this;
    }

    public function setRanges(array $ranges): static
    {
        $this->ranges = array_values(
            array_intersect(
                $ranges,
                resolve_static(TimeFrameEnum::class, 'values')
            )
        );

        return $this;
    }

    public function setStartingDate(string|CarbonImmutable|null $startingDate): static
    {
        $this->startingDate = is_string($startingDate) ? CarbonImmutable::parse($startingDate) : $startingDate;
        $this->endingDate ??= CarbonImmutable::now();

        return $this;
    }

    public function withGrowthRate(bool $withGrowthRate = true): static
    {
        $this->withGrowthRate = $withGrowthRate;

        return $this;
    }

    public function withoutRange(): static
    {
        $this->range = false;

        return $this;
    }

    protected function getDateColumn(): string
    {
        return $this->dateColumn ?? $this->query->getModel()->getQualifiedCreatedAtColumn();
    }

    protected function resolveBetween(array $range): array
    {
        return [
            $this->getDateColumn(),
            $range,
        ];
    }

    protected function transformResult(int|float|string|null $data): string
    {
        return Rounding::round($data ?? 0);
    }
}
