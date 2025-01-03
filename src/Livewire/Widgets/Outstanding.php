<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\Support\Metrics\Value;
use FluxErp\Support\Widgets\ValueBox;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use Illuminate\Support\Number;
use Livewire\Attributes\Renderless;

class Outstanding extends ValueBox
{
    use IsTimeFrameAwareWidget;

    public bool $shouldBePositive = false;

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateSum',
        ];
    }

    #[Renderless]
    public function calculateSum(): void
    {
        $metric = Value::make(
            resolve_static(Order::class, 'query')
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->whereNotState('payment_state', Paid::class)
                ->revenue()
        )
            ->setRange($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start)
            ->setDateColumn('invoice_date')
            ->withGrowthRate()
            ->sum('balance');

        $symbol = Currency::default()->symbol;
        $this->sum = Number::abbreviate($metric->getValue(), 2) . ' ' . $symbol;
        $this->previousSum = Number::abbreviate($metric->getPreviousValue(), 2) . ' ' . $symbol;
        $this->growthRate = $metric->getGrowthRate();
    }
}
