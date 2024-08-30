<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Enums\TimeFrameEnum;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Modelable;

trait IsTimeFrameAwareWidget
{
    #[Modelable]
    public array $timeParams = [];

    public TimeFrameEnum $timeFrame = TimeFrameEnum::ThisMonth;

    public ?Carbon $start = null;

    public ?Carbon $end = null;

    abstract public function calculateByTimeFrame(): void;

    public function updatedTimeParams(): void
    {
        $timeFrame = data_get($this->timeParams, 'timeFrame', TimeFrameEnum::ThisMonth);

        $this->timeFrame = is_string($timeFrame)
            ? TimeFrameEnum::tryFrom($timeFrame)
            : $timeFrame;
        $this->start = $this->timeFrame === TimeFrameEnum::Custom
            ? Carbon::parse(data_get($this->timeParams, 'start'))
            : null;
        $this->end = $this->timeFrame === TimeFrameEnum::Custom
            ? Carbon::parse(data_get($this->timeParams, 'end'))
            : null;

        if ($this->timeFrame === TimeFrameEnum::Custom && ($this->start === null || $this->end === null)) {
            return;
        }

        $this->calculateByTimeFrame();
    }
}
