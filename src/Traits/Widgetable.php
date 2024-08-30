<?php

namespace FluxErp\Traits;

use FluxErp\Enums\TimeFrameEnum;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;

trait Widgetable
{
    #[Modelable]
    public array $timeParams = [];

    public TimeFrameEnum $timeFrame = TimeFrameEnum::ThisMonth;

    public ?Carbon $start = null;

    public ?Carbon $end = null;

    abstract public function calculateByTimeFrame(): void;

    #[On('time-frame-changed')]
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

    public static function getLabel(): string
    {
        if (app()->runningInConsole()) {
            return Str::headline(class_basename(static::class));
        }

        return __(Str::headline(class_basename(static::class)));
    }

    public function placeholder(): View
    {
        if (method_exists(parent::class, 'placeholder')) {
            return parent::placeholder();
        }

        return view('flux::livewire.placeholders.box');
    }
}
