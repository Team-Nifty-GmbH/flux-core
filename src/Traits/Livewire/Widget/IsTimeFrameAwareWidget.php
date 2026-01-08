<?php

namespace FluxErp\Traits\Livewire\Widget;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use FluxErp\Enums\ComparisonTypeEnum;
use FluxErp\Enums\TimeFrameEnum;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;

trait IsTimeFrameAwareWidget
{
    use Widgetable;

    public ?string $comparisonEnd = null;

    public ?string $comparisonStart = null;

    public string $comparisonType = ComparisonTypeEnum::Auto;

    public ?string $end = null;

    public ?string $start = null;

    public string $timeFrame = TimeFrameEnum::ThisMonth;

    #[Modelable]
    public array $timeParams = [];

    abstract public function calculateByTimeFrame(): void;

    public function mountIsTimeFrameAwareWidget(): void
    {
        if ($this->timeFrame !== data_get($this->timeParams, 'timeFrame')) {
            $this->updatedTimeParams();
        }
    }

    public function updatedTimeParams(): void
    {
        $this->applyTimeParams($this->timeParams);
    }

    #[On('dashboard-params-updated')]
    #[Renderless]
    public function onDashboardParamsUpdated(array $params): void
    {
        $this->timeParams = $params;
        $this->applyTimeParams($params);
    }

    protected function applyTimeParams(array $params): void
    {
        $this->timeFrame = resolve_static(
            TimeFrameEnum::class,
            'tryFrom',
            ['value' => data_get($params, 'timeFrame')]
        )
            ?->value
            ?? TimeFrameEnum::ThisMonth;

        $dateRange = data_get($params, 'dateRange', []);
        $this->start = $this->timeFrame === TimeFrameEnum::Custom && data_get($dateRange, 0)
            ? Carbon::parse(data_get($dateRange, 0))->toDateString()
            : null;
        $this->end = $this->timeFrame === TimeFrameEnum::Custom && data_get($dateRange, 1)
            ? Carbon::parse(data_get($dateRange, 1))->toDateString()
            : null;

        $this->comparisonType = resolve_static(
            ComparisonTypeEnum::class,
            'tryFrom',
            ['value' => data_get($params, 'comparisonType')]
        )
            ?->value
            ?? ComparisonTypeEnum::Auto;

        $comparisonRange = data_get($params, 'comparisonRange', []);
        $this->comparisonStart = $this->comparisonType === ComparisonTypeEnum::Custom && data_get($comparisonRange, 0)
            ? Carbon::parse(data_get($comparisonRange, 0))->toDateString()
            : null;
        $this->comparisonEnd = $this->comparisonType === ComparisonTypeEnum::Custom && data_get($comparisonRange, 1)
            ? Carbon::parse(data_get($comparisonRange, 1))->toDateString()
            : null;

        if ($this->timeFrame === TimeFrameEnum::Custom && ($this->start === null || $this->end === null)) {
            return;
        }

        if ($this->comparisonType === ComparisonTypeEnum::Custom && ($this->comparisonStart === null || $this->comparisonEnd === null)) {
            return;
        }

        $this->calculateByTimeFrame();
    }

    protected function getEnd(): Carbon|CarbonImmutable|null
    {
        if ($this->timeFrame === TimeFrameEnum::Custom && $this->end) {
            return Carbon::parse($this->end)->endOfDay();
        }

        if ($this->comparisonType !== ComparisonTypeEnum::Auto) {
            return $this->getFullPeriodEnd();
        }

        return data_get(
            resolve_static(TimeFrameEnum::class, 'getRange', ['case' => $this->timeFrame]),
            1
        )
            ?->endOfDay();
    }

    protected function getFullPeriodEnd(): Carbon|CarbonImmutable|null
    {
        $now = Carbon::now();

        return match ($this->timeFrame) {
            TimeFrameEnum::Today, TimeFrameEnum::Yesterday => $now->endOfDay(),
            TimeFrameEnum::ThisWeek => $now->endOfWeek()->endOfDay(),
            TimeFrameEnum::ThisMonth, TimeFrameEnum::LastMonth => $now->endOfMonth()->endOfDay(),
            TimeFrameEnum::ThisQuarter => $now->endOfQuarter()->endOfDay(),
            TimeFrameEnum::ThisYear => $now->endOfYear()->endOfDay(),
            default => $now->endOfDay(),
        };
    }

    protected function getEndPrevious(): Carbon|CarbonImmutable|null
    {
        if ($this->comparisonType === ComparisonTypeEnum::Custom && $this->comparisonEnd) {
            return Carbon::parse($this->comparisonEnd)->endOfDay();
        }

        if ($this->comparisonType === ComparisonTypeEnum::PreviousMonth) {
            return $this->getEnd()->subMonthNoOverflow()->endOfDay();
        }

        if ($this->comparisonType === ComparisonTypeEnum::PreviousQuarter) {
            return $this->getEnd()->subMonthsNoOverflow(3)->endOfDay();
        }

        if ($this->comparisonType === ComparisonTypeEnum::PreviousYear) {
            return $this->getEnd()->subYear()->endOfDay();
        }

        if ($this->timeFrame === TimeFrameEnum::Custom) {
            return match (true) {
                $this->getStart()->isStartOfMonth() && $this->getEnd()->isEndOfMonth() => $this->getEnd()
                    ->subMonthNoOverflow()
                    ->endOfMonth()
                    ->endOfDay(),
                $this->getStart()->isStartOfYear() && $this->getEnd()->isEndOfYear() => $this->getEnd()
                    ->subYear()
                    ->endOfYear(),
                default => $this->getEnd()->subDays(round($this->getStart()->diffInDays($this->getEnd())) ?: 1)
            };
        }

        return data_get(
            resolve_static(TimeFrameEnum::class, 'getPreviousRange', ['case' => $this->timeFrame]),
            1
        )
            ->endOfDay();
    }

    protected function getStart(): Carbon|CarbonImmutable|null
    {
        return $this->timeFrame === TimeFrameEnum::Custom && $this->start
            ? Carbon::parse($this->start)->startOfDay()
            : data_get(
                resolve_static(TimeFrameEnum::class, 'getRange', ['case' => $this->timeFrame]),
                0
            )
                ?->startOfDay();
    }

    protected function getStartPrevious(): Carbon|CarbonImmutable|null
    {
        if ($this->comparisonType === ComparisonTypeEnum::Custom && $this->comparisonStart) {
            return Carbon::parse($this->comparisonStart)->startOfDay();
        }

        if ($this->comparisonType === ComparisonTypeEnum::PreviousMonth) {
            return $this->getStart()->subMonthNoOverflow()->startOfDay();
        }

        if ($this->comparisonType === ComparisonTypeEnum::PreviousQuarter) {
            return $this->getStart()->subMonthsNoOverflow(3)->startOfDay();
        }

        if ($this->comparisonType === ComparisonTypeEnum::PreviousYear) {
            return $this->getStart()->subYear()->startOfDay();
        }

        if ($this->timeFrame === TimeFrameEnum::Custom) {
            return match (true) {
                $this->getStart()->isStartOfMonth() && $this->getEnd()->isEndOfMonth() => $this->getStart()
                    ->subMonthNoOverflow()
                    ->startOfMonth()
                    ->startOfDay(),
                $this->getStart()->isStartOfYear() && $this->getEnd()->isEndOfYear() => $this->getStart()
                    ->subYear()
                    ->startOfYear(),
                default => $this->getStart()->subDays(round($this->getStart()->diffInDays($this->getEnd())) ?: 1)
            };
        }

        return data_get(
            resolve_static(TimeFrameEnum::class, 'getPreviousRange', ['case' => $this->timeFrame]),
            0
        )
            ->startOfDay();
    }

    protected function getComparisonEnd(): Carbon|CarbonImmutable|null
    {
        return $this->getEndPrevious();
    }

    protected function getComparisonStart(): Carbon|CarbonImmutable|null
    {
        return $this->getStartPrevious();
    }

    protected function getUnit(): ?string
    {
        $unit = resolve_static(TimeFrameEnum::class, 'getUnit', ['case' => $this->timeFrame]);

        if ($unit === null && $this->start && $this->end) {
            $diff = Carbon::parse($this->start)->diffInDays(Carbon::parse($this->end));

            return match (true) {
                $diff <= 31 => 'day',
                $diff <= 365 => 'month',
                default => 'year',
            };
        }

        return $unit;
    }
}
