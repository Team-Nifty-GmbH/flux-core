<?php

namespace FluxErp\Livewire\Widgets;

use Carbon\Carbon;
use Cron\CronExpression;
use FluxErp\Models\Pivots\OrderSchedule;
use FluxErp\Support\Widgets\Charts\BarChart;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use Livewire\Attributes\Renderless;

class RecurringRevenueForecast extends BarChart
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait;

    public ?array $chart = [
        'type' => 'bar',
        'stacked' => true,
    ];

    public ?array $plotOptions = [
        'bar' => [
            'horizontal' => false,
            'endingShape' => 'rounded',
            'columnWidth' => '70%',
        ],
    ];

    public bool $showTotals = true;

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateChart();
    }

    public function calculateChart(): void
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->addMonths(12)->endOfMonth();
        $currentMonthIndex = Carbon::now()->month - 1;

        $orderSchedules = OrderSchedule::with(['order', 'schedule'])->get();

        $clientsData = [];

        foreach ($orderSchedules as $orderSchedule) {
            if ($orderSchedule->schedule->cron_expression) {
                $order = $orderSchedule->order;
                $client = $order->client->name ?? 'Unknown';

                $orderValue = $order->total_net_price;
                $cron = new CronExpression($orderSchedule->schedule->cron_expression);
                $nextRun = $cron->getNextRunDate($start);

                while ($nextRun <= $end) {
                    $month = Carbon::parse($nextRun)->monthName;
                    $clientsData[$client][$month] = ($clientsData[$client][$month] ?? 0) + $orderValue;
                    $nextRun = $cron->getNextRunDate($nextRun);
                }
            }
        }

        $months = [
            __('January'), __('February'), __('March'), __('April'), __('May'), __('June'),
            __('July'), __('August'), __('September'), __('October'), __('November'), __('December'),
        ];

        $orderedMonths = array_merge(
            array_slice($months, $currentMonthIndex),
            array_slice($months, 0, $currentMonthIndex)
        );

        $series = [];

        foreach ($clientsData as $clientName => $monthlyData) {
            $data = [];

            foreach ($orderedMonths as $month) {
                $data[] = $monthlyData[$month] ?? 0;
            }

            $series[] = [
                'name' => $clientName,
                'data' => $data,
            ];
        }

        $this->series = $series;
        $this->xaxis['categories'] = $orderedMonths;
    }

    public function showTitle(): bool
    {
        return true;
    }
}
