<?php

namespace FluxErp\Livewire\Widgets;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Cron\CronExpression;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\Pivots\OrderSchedule;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use FluxErp\Traits\Widgetable;
use Livewire\Attributes\Renderless;

class RecurringRevenueForecast extends BarChart
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait, Widgetable;

    public ?array $chart = [
        'type' => 'bar',
        'stacked' => true,
    ];

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

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

    private function createFormattedPeriodDay(Carbon $date): string
    {
        return $date->day . ' ' . $date->monthName;
    }

    private function createFormattedPeriodMonth(Carbon $date): string
    {
        return $date->monthName . ' ' . $date->year;
    }

    private function createFormattedPeriodQuarter(Carbon $date): string
    {
        $quarter = ceil($date->month / 3);
        return 'Q' . $quarter . ' ' . $date->year;
    }

    public function calculateChart(): void
    {
        $locale = auth()->user()?->language->language_code ?? 'de';
        Carbon::setLocale($locale);

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

        $diffInDays = $startDate->diffInDays($endDate);
        if ($diffInDays <= 31) {
            $interval = '1 day';
        } elseif ($diffInDays <= 365) {
            $interval = '1 month';
        } else {
            $interval = '3 months';
        }

        $period = CarbonPeriod::create($startDate, $interval, $endDate);

        $startDate = $startDate->subSecond(); // subtract 1 second to avoid issues with getNextRunDate

        $formattedPeriods = [];
        foreach ($period as $date) {
            switch ($interval) {
                case '1 day':
                    $formattedPeriods[] = $this->createFormattedPeriodDay($date);
                    break;
                case '1 month':
                    $formattedPeriods[] = $this->createFormattedPeriodMonth($date);
                    break;
                case '3 months':
                    $formattedPeriods[] = $this->createFormattedPeriodQuarter($date);
                    break;
            }
        }

        $orderSchedules = resolve_static(OrderSchedule::class, 'query')
            ->whereHas('schedule', function($query) {
                $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('ends_at')
                            ->orWhere('ends_at', '>', Carbon::now());
                    });
            })
            ->with([
                'order' => function ($query) {
                    $query->select(['id', 'client_id', 'total_net_price'])
                        ->with(['client' => function ($q) {
                            $q->select(['id', 'name']);
                        }]);
                },
                'schedule' => function ($query) {
                    $query->where('is_active', true)
                        ->select(['id', 'cron_expression', 'created_at', 'ends_at', 'is_active']);
                }
            ])
            ->get();

        $series = [];
        $seriesIndexMap = [];

        foreach ($orderSchedules as $orderSchedule) {
            $effectiveStartDate = $startDate;
            if ($orderSchedule->schedule->cron_expression && $orderSchedule->schedule->is_active) {
                if ($orderSchedule->schedule->created_at > $effectiveStartDate) {
                    $effectiveStartDate = $orderSchedule->schedule->created_at;
                }
                $order       = $orderSchedule->order;
                $clientName  = $order->client->name ?? __('Unknown');
                $orderValue  = $order->total_net_price;
                $cron        = new CronExpression($orderSchedule->schedule->cron_expression);
                $nextRun     = $cron->getNextRunDate($effectiveStartDate);

                if (! isset($seriesIndexMap[$clientName])) {
                    $seriesIndexMap[$clientName] = count($series);
                    $series[] = [
                        'name' => $clientName,
                        'data' => array_fill(0, count($formattedPeriods), 0),
                    ];
                }

                $clientIdx = $seriesIndexMap[$clientName];

                while (
                    $nextRun <= $endDate
                    && (! $orderSchedule->schedule->ends_at || $nextRun <= $orderSchedule->schedule->ends_at)
                ) {
                    $nextRunTime = Carbon::parse($nextRun);

                    switch ($interval) {
                        case '1 day':
                            $label = $this->createFormattedPeriodDay($nextRunTime);
                            break;
                        case '1 month':
                            $label = $this->createFormattedPeriodMonth($nextRunTime);
                            break;
                        case '3 months':
                            $label = $this->createFormattedPeriodQuarter($nextRunTime);
                            break;
                        default:
                            $label = '';
                    }

                    if (false !== $idx = array_search($label, $formattedPeriods)) {
                        $series[$clientIdx]['data'][$idx] = bcadd(
                            $series[$clientIdx]['data'][$idx],
                            $orderValue
                        );
                    }

                    $nextRun = $cron->getNextRunDate($nextRun);
                }
            }
        }

        $this->series = $series;
        $this->xaxis['categories'] = $formattedPeriods;
    }

    public function showTitle(): bool
    {
        return true;
    }
}
