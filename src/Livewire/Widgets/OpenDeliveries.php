<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\Order;
use FluxErp\States\Order\DeliveryState\Open;
use FluxErp\Support\Metrics\Results\ValueResult;
use FluxErp\Support\Metrics\Value;
use FluxErp\Support\Widgets\ValueBox;

class OpenDeliveries extends ValueBox
{
    public bool $shouldBePositive = false;

    /**
     * @throws \Exception
     */
    public function calculateSum(): void
    {
        /** @var ValueResult $metric */
        $metric = Value::make(
            resolve_static(Order::class, 'query')
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->whereState('delivery_state', Open::class)
                ->revenue()
        )
            ->range($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start)
            ->dateColumn('invoice_date')
            ->withGrowthRate()
            ->count('id');

        $this->sum = $metric->getValue();
        $this->previousSum = $metric->getPreviousValue();
        $this->growthRate = $metric->getGrowthRate();
    }
}
