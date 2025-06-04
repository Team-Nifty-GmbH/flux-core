<?php

namespace FluxErp\Traits\Livewire;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Traits\Widgetable;
use Livewire\Attributes\Modelable;

trait IsTimeFrameAwareWidget
{
    use Widgetable;

    public ?string $end = null;

    public ?string $start = null;

    public TimeFrameEnum $timeFrame = TimeFrameEnum::ThisMonth;

    #[Modelable]
    public array $timeParams = [];

    abstract public function calculateByTimeFrame(): void;

    public function updatedTimeParams(): void
    {
        $timeFrame = data_get($this->timeParams, 'timeFrame', TimeFrameEnum::ThisMonth);

        $this->timeFrame = is_string($timeFrame)
            ? TimeFrameEnum::tryFrom($timeFrame)
            : $timeFrame;
        $this->start = $this->timeFrame === TimeFrameEnum::Custom
            ? Carbon::parse(data_get($this->timeParams, 'start'))->toDateString()
            : null;
        $this->end = $this->timeFrame === TimeFrameEnum::Custom
            ? Carbon::parse(data_get($this->timeParams, 'end'))->toDateString()
            : null;

        if ($this->timeFrame === TimeFrameEnum::Custom && ($this->start === null || $this->end === null)) {
            return;
        }

        $this->calculateByTimeFrame();
    }

    protected function getEnd(): Carbon|CarbonImmutable|null
    {
        return $this->timeFrame === TimeFrameEnum::Custom && $this->end
            ? Carbon::parse($this->end)->endOfDay()
            : data_get($this->timeFrame->getRange(), 1)?->endOfDay();
    }

    protected function getEndPrevious(): Carbon|CarbonImmutable|null
    {
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

        return data_get($this->timeFrame->getPreviousRange(), 1)->endOfDay();
    }

    protected function getStart(): Carbon|CarbonImmutable|null
    {
        return $this->timeFrame === TimeFrameEnum::Custom && $this->start
            ? Carbon::parse($this->start)->endOfDay()
            : data_get($this->timeFrame->getRange(), 0)?->startOfDay();
    }

    protected function getStartPrevious(): Carbon|CarbonImmutable|null
    {
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

        return data_get($this->timeFrame->getPreviousRange(), 0)->startOfDay();
    }
}
