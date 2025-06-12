<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\LineChart;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Support\Metrics\Results\Result;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use Livewire\Attributes\Renderless;

class RevenuePurchasesProfitChart extends LineChart
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
            $revenueValue = $revenue->getCombinedData()[$key] ?? 0;
            $purchasesValue = $purchases->getCombinedData()[$key] ?? 0;
            $profitValue = $profit->getCombinedData()[$key] ?? 0;

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

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateByTimeFrame',
        ];
    }
}
