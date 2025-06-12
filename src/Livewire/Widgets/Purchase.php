<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Value;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class Purchase extends ValueBox implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget;

    public bool $shouldBePositive = false;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    #[Renderless]
    public function calculateSum(): void
    {
        $metric = Value::make(
            resolve_static(Order::class, 'query')
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->purchase()
        )
            ->setRange($this->timeFrame)
            ->setEndingDate($this->getEnd())
            ->setStartingDate($this->getStart())
            ->setDateColumn('invoice_date')
            ->withGrowthRate()
            ->sum('total_net_price');

        $symbol = resolve_static(Currency::class, 'default')->symbol;
        $this->sum = Number::abbreviate($metric->getValue(), 2) . ' ' . $symbol;
        $this->previousSum = Number::abbreviate($metric->getPreviousValue(), 2) . ' ' . $symbol;
        $this->growthRate = $metric->getGrowthRate();
    }

    public function options(): array
    {
        return [
            [
                'label' => static::getLabel(),
                'method' => 'show',
            ],
            [
                'label' => __('Previous Period'),
                'method' => 'showPrevious',
            ],
        ];
    }

    #[Renderless]
    public function show(): void
    {
        $startCarbon = $this->getStart();
        $endCarbon = $this->getEnd();

        $start = $startCarbon->toDateString();
        $end = $endCarbon->toDateString();

        $localizedStart = $startCarbon->translatedFormat('j. F Y');
        $localizedEnd = $endCarbon->translatedFormat('j. F Y');

        SessionFilter::make(
            Livewire::new(resolve_static(OrderList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->purchase()
                ->whereBetween('invoice_date', [
                    $start,
                    $end,
                ]),
            __('Purchase') . ' ' . __('between :start and :end', ['start' => $localizedStart, 'end' => $localizedEnd]),
        )
            ->store();

        $this->redirectRoute('orders.orders', navigate: true);
    }

    #[Renderless]
    public function showPrevious(): void
    {
        $startCarbon = $this->getStartPrevious();
        $endCarbon = $this->getEndPrevious();

        $localizedStart = $startCarbon->translatedFormat('j. F Y');
        $localizedEnd = $endCarbon->translatedFormat('j. F Y');

        $start = $startCarbon->toDateString();
        $end = $endCarbon->toDateString();

        SessionFilter::make(
            Livewire::new(resolve_static(OrderList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->purchase()
                ->whereBetween('invoice_date', [
                    $start,
                    $end,
                ]),
            __('Purchase') . ' ' . __('between :start and :end', ['start' => $localizedStart, 'end' => $localizedEnd]),
        )
            ->store();

        $this->redirectRoute('orders.orders', navigate: true);
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateSum',
        ];
    }
}
