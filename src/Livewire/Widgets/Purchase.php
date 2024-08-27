<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Results\ValueResult;
use FluxErp\Support\Metrics\Value;
use FluxErp\Support\Widgets\ValueBox;
use Illuminate\Support\Number;

class Purchase extends ValueBox
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
                ->purchase()
        )
            ->range($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start)
            ->dateColumn('invoice_date')
            ->withGrowthRate()
            ->sum('total_net_price');

        $symbol = Currency::default()->symbol;
        $this->sum = Number::abbreviate($metric->getValue(), 2) . ' ' . $symbol;
        $this->previousSum = Number::abbreviate($metric->getPreviousValue(), 2) . ' ' . $symbol;
        $this->growthRate = $metric->getGrowthRate();
    }
}
