<?php

namespace FluxErp\Livewire\Widgets;

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

    public string $timeFrame = TimeFrameEnum::LastMonth->name;

    public static function getLabel(): string
    {
        return __('Total Profit');
    }

    public function mount(): void
    {
        $this->calculateSum();
    }

    public function render(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('flux::livewire.widgets.total-profit',
            [
                'currency' => Currency::query()->where('is_default', true)->first()->toArray(),
                'timeFrames' => array_map(function (TimeFrameEnum $timeFrame) {
                    return [
                        'value' => $timeFrame->name,
                        'label' => __($timeFrame->value),
                    ];
                }, TimeFrameEnum::cases()),
                'selectedTimeFrame' => $this->timeFrame,
            ]
        );
    }

    public function updatedTimeFrame(): void
    {
        $this->calculateSum();
        $this->skipRender();
    }

    /**
     * @throws \Exception
     */
    public function calculateSum(): void
    {
        $query = Order::query();

        $timeFrame = TimeFrameEnum::fromName($this->timeFrame);
        $parameters = $timeFrame->dateQueryParameters();

        if ($parameters && count($parameters) > 0) {
            if ($parameters[1] === 'between') {
                $query->whereBetween($parameters[0], $parameters[2]);
            } else {
                $query->where(...$parameters);
            }
        }

        $this->sum = round($query->sum('margin'), 2);
    }
}
