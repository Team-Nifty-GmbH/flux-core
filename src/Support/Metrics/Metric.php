<?php

namespace FluxErp\Support\Metrics;

use Carbon\CarbonImmutable;
use FluxErp\Enums\GrowthRateTypeEnum;
use FluxErp\Enums\TimeFrameEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;

abstract class Metric
{
    use Conditionable, Macroable;

    protected Builder $query;

    protected string $type;

    protected string $column;

    protected int|TimeFrameEnum|null $range = null;

    protected ?CarbonImmutable $startingDate = null;

    protected ?CarbonImmutable $endingDate = null;

    protected bool $withGrowthRate = false;

    protected ?string $dateColumn = null;

    protected GrowthRateTypeEnum $growthRateType = GrowthRateTypeEnum::Percentage;

    protected array $ranges = [
        15,
        30,
        60,
        365,
        TimeFrameEnum::Today,
        TimeFrameEnum::Yesterday,
        TimeFrameEnum::ThisWeek,
        TimeFrameEnum::ThisMonth,
        TimeFrameEnum::ThisQuarter,
        TimeFrameEnum::ThisYear,
        TimeFrameEnum::Custom,
    ];

    public function __construct(
        string|Builder $query,
    ) {
        $this->query = is_string($query) ? $query::query() : $query->clone();
    }

    public static function make(string|Builder $query): static
    {
        return app(static::class, ['query' => $query]);
    }

    abstract protected function resolve(): mixed;

    public function setStartingDate(string|CarbonImmutable|null $startingDate): static
    {
        $this->startingDate = is_string($startingDate) ? CarbonImmutable::parse($startingDate) : $startingDate;
        $this->endingDate ??= CarbonImmutable::now();

        return $this;
    }

    public function setEndingDate(string|CarbonImmutable|null $endingDate): static
    {
        $this->endingDate = is_string($endingDate) ? CarbonImmutable::parse($endingDate) : $endingDate;

        return $this;
    }

    public function modifyQuery(callable $callback): static
    {
        $callback($this->query);

        return $this;
    }

    public function withGrowthRate(bool $withGrowthRate = true): static
    {
        $this->withGrowthRate = $withGrowthRate;

        return $this;
    }

    public function growthRateType(GrowthRateTypeEnum $growthRateType): static
    {
        $this->growthRateType = $growthRateType;

        return $this;
    }

    public function range(int|string|TimeFrameEnum|null $range): static
    {
        if (! $range instanceof TimeFrameEnum && ! is_null($range)) {
            $range = TimeFrameEnum::tryFrom($range);
        }

        if (in_array($range, $this->getRanges())) {
            $this->range = $range;
        }

        return $this;
    }

    public function ranges(array $ranges): static
    {
        $this->ranges = Arr::map($ranges,
            fn ($range) => is_string($range) ? TimeFrameEnum::from($range) : $range
        );

        return $this;
    }

    public function rangesFromOptions(array $options): static
    {
        return $this->ranges(array_keys($options));
    }

    public function getRange(): int|TimeFrameEnum
    {
        return $this->range ?? $this->getRanges()[0] ?? TimeFrameEnum::ThisWeek;
    }

    public function getRanges(): array
    {
        return $this->ranges;
    }

    public function dateColumn(string $dateColumn): static
    {
        $this->dateColumn = $dateColumn;

        return $this;
    }

    protected function getDateColumn(): string
    {
        return $this->dateColumn ?? $this->query->getModel()->getCreatedAtColumn();
    }

    protected function resolveBetween(array $range): array
    {
        return [
            $this->getDateColumn(),
            $range,
        ];
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

        if ($range instanceof TimeFrameEnum) {
            return $range->getPreviousRange();
        }

        return [
            Date::now()->subDays($range * 2),
            Date::now()->subDays($range),
        ];
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

        if ($range instanceof TimeFrameEnum) {
            return $range->getRange();
        }

        return [
            Date::now()->subDays($range),
            Date::now(),
        ];
    }

    protected function transformResult(int|float $data): float
    {
        return round($data, 2);
    }
}
