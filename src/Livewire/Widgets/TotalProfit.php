<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class TotalProfit extends Component
{
    use Widgetable;

    public float $sum = 0;

    #[Modelable]
    public TimeFrameEnum $timeFrame;

    public function mount(): void
    {
        $this->calculateSum();
    }

    public function render(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('flux::livewire.widgets.total-profit',
            [
                'currency' => Currency::default()?->toArray(),
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
        $query = resolve_static(Order::class, 'query');

        $timeFrame = $this->timeFrame;
        $parameters = $timeFrame->dateQueryParameters('invoice_date');

        if ($parameters && count($parameters) > 0) {
            if ($parameters['operator'] === 'between') {
                $query->whereBetween($parameters['column'], $parameters['value']);
            } else {
                $query->where(...array_values($parameters));
            }
        }

        $this->sum = round($query->sum('margin'), 2);
    }
}
