<?php

namespace FluxErp\Livewire\Widgets;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
        $this->skipRender();
        $this->calculateChart();
        $this->updateData();
    }

    public function calculateChart(): void
    {
        $months = [
            __('January'),
            __('February'),
            __('March'),
            __('April'),
            __('May'),
            __('June'),
            __('July'),
            __('August'),
            __('September'),
            __('October'),
            __('November'),
            __('December'),
        ];

        $range = $this->timeFrame?->getRange();

        if (is_array($range) && count($range) === 2 && $range[0] && $range[1]) {
            $startDate = Carbon::parse($range[0]);
            $endDate = Carbon::parse($range[1]);
        } elseif ($this->start && $this->end) {
            $startDate = Carbon::parse($this->start);
            $endDate = Carbon::parse($this->end);
        } else {
            $startDate = now();
            $endDate = now()->addYear();
        }

        $period = CarbonPeriod::create($startDate, '1 month', $endDate);

        $startDate = $startDate->subSecond(1); // subtract 1 second to avoid issues with getNextRunDate

        $formattedMonths = [];
        foreach ($period as $date) {
            $monthIndex = $date->month - 1;
            $formattedMonths[] = $months[$monthIndex] . ' ' . $date->year;
        }

        $orderSchedules = resolve_static(OrderSchedule::class, 'query')
            ->with(['order', 'schedule'])
            ->get();

        $clientsData = [];

        foreach ($orderSchedules as $orderSchedule) {
            $lStartDate = $startDate;
            if ($orderSchedule->schedule->cron_expression && $orderSchedule->schedule->is_active) {
                if ($orderSchedule->schedule->created_at > $lStartDate) {
                    $lStartDate = $orderSchedule->schedule->created_at;
                }
                $order = $orderSchedule->order;
                $client = $order->client->name ?? __('Unknown');

                $orderValue = $order->total_net_price;
                $cron = new CronExpression($orderSchedule->schedule->cron_expression);
                $nextRun = $cron->getNextRunDate($lStartDate);

                while ($nextRun <= $endDate &&
                    (! $orderSchedule->schedule->ends_at || $nextRun <= $orderSchedule->schedule->ends_at)) {
                    $nextRunTime = Carbon::parse($nextRun);
                    $month = $nextRunTime->monthName . ' ' . $nextRunTime->year;
                    $clientsData[$client][$month] = bcadd($clientsData[$client][$month] ?? 0, $orderValue);
                    $nextRun = $cron->getNextRunDate($nextRun);
                }
            }
        }

        $series = [];

        foreach ($clientsData as $clientName => $monthlyData) {
            $data = [];

            foreach ($formattedMonths as $month) {
                $data[] = $monthlyData[$month] ?? 0;
            }

            $series[] = [
                'name' => $clientName,
                'data' => $data,
            ];
        }

        $this->series = $series;
        $this->xaxis['categories'] = $formattedMonths;
    }

    public function showTitle(): bool
    {
        return true;
    }
}
