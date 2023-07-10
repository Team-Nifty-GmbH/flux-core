<?php

namespace FluxErp\Http\Livewire\Widgets;

use FluxErp\Contracts\UserWidget;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

class TotalProfit extends Component implements UserWidget
{
    public float $sum = 0;

    public string $timeFrame = TimeFrameEnum::LastMonth->value;

    public function mount(): void
    {
        $this->calculateSum();
    }

    public function timeFrameUpdated(): void
    {
        $this->calculateSum();
    }

    /**
     * @throws \Exception
     */
    public function calculateSum(): void
    {
        $query = Order::query();
        $timeFrame = TimeFrameEnum::from($this->timeFrame);
        $parameters = $timeFrame->dateQueryParameters();

        if ($parameters && count($parameters) > 0) {
            if ($parameters[1] === 'between') {
                $query->whereBetween($parameters[0], $parameters[2]);
            } else {
                $query->where(...$parameters);
            }
        }

        $sum = $query->sum('margin');
        $this->sum = round($sum, 2);
    }

    public function render(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('flux::livewire.widgets.total-profit',
            [
                'currency' => Currency::query()->where('is_default', true)->first()->toArray(),
                'timeFrames' => TimeFrameEnum::cases(),
                'selectedTimeFrame' => $this->timeFrame,
            ]
        );
    }

    public static function getLabel(): string
    {
        return __('Total Profit');
    }
}
