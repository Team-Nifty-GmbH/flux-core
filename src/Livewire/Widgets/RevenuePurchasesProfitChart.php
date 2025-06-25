<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Support\Widgets\Charts\LineChart;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Support\Metrics\Results\Result;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class RevenuePurchasesProfitChart extends LineChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->skipRender();
        $this->calculateChart();
        $this->updateData();
    }

    public function calculateChart(): void
    {
        $baseQuery = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number');

        $revenue = Line::make($baseQuery->clone()->revenue())
            ->setDateColumn('invoice_date')
            ->setRange($this->timeFrame)
            ->setEndingDate($this->getEnd())
            ->setStartingDate($this->getStart())
            ->sum('total_net_price');

        $purchases = Line::make($baseQuery->clone()->purchase())
            ->setDateColumn('invoice_date')
            ->setRange($this->timeFrame)
            ->setEndingDate($this->getEnd())
            ->setStartingDate($this->getStart())
            ->sum('total_net_price');

        $purchases->setData(
            array_map(fn ($value) => (int) bcmul($value, -1, 0), $purchases->getData())
        );
        $purchasesData = $purchases->getCombinedData();

        $profit = [];
        foreach ($revenue->getCombinedData() as $key => $value) {
            $profit[$key] = (int) bcadd($value, data_get($purchasesData, $key, 0), 0);
        }
        $profit = Result::make(array_values($profit), array_keys($profit), null);

        $keys = array_unique(array_merge($revenue->getLabels(), $purchases->getLabels(), $profit->getLabels()));
        $revenue->mergeLabels($keys);
        $purchases->mergeLabels($keys);
        $profit->mergeLabels($keys);

        // remove all values that are zero in all series
        foreach ($keys as $key) {
            $revenueValue = data_get($revenue->getCombinedData(), $key) ?? 0;
            $purchasesValue = data_get($purchases->getCombinedData(), $key) ?? 0;
            $profitValue = data_get($profit->getCombinedData(), $key) ?? 0;

            if ($revenueValue === 0 && $purchasesValue === 0 && $profitValue === 0) {
                $revenue->removeLabel($key);
                $purchases->removeLabel($key);
                $profit->removeLabel($key);
            }
        }

        $this->series = [
            [
                'name' => __('Revenue'),
                'color' => 'emerald',
                'data' => $revenue->getData(),
            ],
            [
                'name' => __('Purchases'),
                'color' => 'red',
                'data' => $purchases->getData(),
            ],
            [
                'name' => __('Profit'),
                'color' => 'indigo',
                'data' => $profit->getData(),
            ],
        ];

        $this->xaxis['categories'] = $keys;
    }

    #[Renderless]
    public function options(): array
    {
        return [
            [
                'label' => __('Revenue'),
                'method' => 'redirectByType',
                'params' => 'revenue',
            ],
            [
                'label' => __('Purchases'),
                'method' => 'redirectByType',
                'params' => 'purchases',
            ],
        ];
    }

    #[Renderless]
    public function redirectByType(string $type): void
    {
        $type = Str::headline($type);
        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        if ($type === 'Purchases') {
            $closure = fn (Builder $query) => $query
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->purchase()
                ->whereBetween('invoice_date', [$start, $end]);
        } elseif ($type === 'Revenue') {
            $closure = fn (Builder $query) => $query
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->revenue()
                ->whereBetween('invoice_date', [$start, $end]);
        } else {
            return;
        }

        SessionFilter::make(
            Livewire::new(resolve_static(OrderList::class, 'class'))->getCacheKey(),
            $closure,
            __($type) . ' ' . __('between :start and :end', ['start' => $start, 'end' => $end])
        )->store();

        $this->redirectRoute('orders.orders', navigate: true);
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateByTimeFrame',
        ];
    }
}
