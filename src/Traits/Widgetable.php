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
    public TimeFrameEnum $timeFrame = TimeFrameEnum::LastMonth;

    public ?Carbon $start = null;

    public ?Carbon $end = null;

    #[On('time-frame-changed')]
    public function timeFrameChanged(TimeFrameEnum $timeFrameEnum, ?string $start = null, ?string $end = null): void
    {
        $this->timeFrame = $timeFrameEnum;
        $this->start = Carbon::parse($start);
        $this->end = Carbon::parse($end);

        if (method_exists($this, 'updatedTimeFrame')) {
            $this->updatedTimeFrame();
        }
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
