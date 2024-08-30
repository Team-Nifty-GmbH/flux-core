<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\Order;
use FluxErp\States\Order\DeliveryState\Open;
use FluxErp\Support\Metrics\Value;
use FluxErp\Support\Widgets\ValueBox;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;

class OpenDeliveries extends ValueBox
{
    use IsTimeFrameAwareWidget;

    public bool $shouldBePositive = false;

    public function calculateSum(): void
    {
        $metric = Value::make(
            resolve_static(Order::class, 'query')
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->whereState('delivery_state', Open::class)
                ->revenue()
        )
            ->setRange($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start)
            ->setDateColumn('invoice_date')
            ->withGrowthRate()
            ->count('id');

        $this->sum = $metric->getValue();
        $this->previousSum = $metric->getPreviousValue();
        $this->growthRate = $metric->getGrowthRate();
    }
}
