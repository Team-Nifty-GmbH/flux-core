<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Value;
use FluxErp\Support\Widgets\ValueBox;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use Illuminate\Support\Number;
use Livewire\Attributes\Renderless;

class Revenue extends ValueBox
{
    use IsTimeFrameAwareWidget;

    #[Renderless]
    public function calculateSum(): void
    {
        $metric = Value::make(
            resolve_static(Order::class, 'query')
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->revenue()
        )
            ->setRange($this->timeFrame)
            ->setEndingDate($this->end?->endOfDay())
            ->setStartingDate($this->start?->startOfDay())
            ->setDateColumn('invoice_date')
            ->withGrowthRate()
            ->sum('total_net_price');

        $symbol = resolve_static(Currency::class, 'default')->symbol;
        $this->sum = Number::abbreviate($metric->getValue(), 2) . ' ' . $symbol;
        $this->previousSum = Number::abbreviate($metric->getPreviousValue(), 2) . ' ' . $symbol;
        $this->growthRate = $metric->getGrowthRate();
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateSum',
        ];
    }
}
