<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Traits\Widgetable;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Modelable;

trait IsTimeFrameAwareWidget
{
    use Widgetable;

    public ?Carbon $end = null;

    public ?Carbon $start = null;

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
