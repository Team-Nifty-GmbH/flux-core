<?php

namespace FluxErp\Livewire\Widgets;

use Cron\CronExpression;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\Pivots\OrderSchedule;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Throwable;

class RecurringRevenueForecast extends BarChart
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait, Widgetable;

    public ?array $chart = [
        'type' => 'bar',
    ];

    public ?array $plotOptions = [
        'bar' => [
            'horizontal' => true,
            'endingShape' => 'rounded',
            'columnWidth' => '70%',
        ],
    ];

    public bool $showTotals = true;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateChart();
        $this->updateData();
    }

    public function calculateChart(): void
    {
        $orderSchedules = resolve_static(OrderSchedule::class, 'query')
            ->whereHas('schedule', function (Builder $query): void {
                $query
                    ->where(fn (Builder $query) => $query
                        ->where('ends_at', '>', now()->toDateTimeString())
                        ->orWhereNull('ends_at')
                    )
                    ->where(fn (Builder $query) => $query
                        ->whereRaw('recurrences > COALESCE(current_recurrence,0)')
                        ->orWhereNull('recurrences')
                    )
                    ->where('is_active', true);
            })
            ->with([
                'order:id,client_id,total_net_price',
                'order.client:id,name',
                'schedule:id,cron_expression,ends_at,recurrences,current_recurrence',
            ])
            ->get();

        $series = [];
        foreach ($orderSchedules as $orderSchedule) {
            if (is_null($orderSchedule->schedule->cron_expression)) {
                continue;
            }

            $cron = new CronExpression($orderSchedule->schedule->cron_expression);
            try {
                $nextRun = $cron->getNextRunDate();
            } catch (Throwable) {
                continue;
            }

            $index = $orderSchedule->order->client_id;
            $currentRecurrence = $orderSchedule->schedule->current_recurrence;
            while (
                $nextRun <= $this->getEnd()
                && $nextRun >= $this->getStart()
                && (
                    is_null($orderSchedule->schedule->ends_at)
                    || $nextRun <= $orderSchedule->schedule->ends_at
                )
                && (
                    is_null($orderSchedule->schedule->recurrencs)
                    || $currentRecurrence < $orderSchedule->schedule->recurrences
                )
            ) {
                $client = $orderSchedule->order->client;
                if (! data_get($series, $index . '.name')) {
                    data_set($series, $index . '.name', $client->name);
                }

                data_set(
                    $series,
                    $index . '.data.0',
                    bcadd(
                        data_get($series, $index . '.data.0') ?? 0,
                        $orderSchedule->order->total_net_price ?? 0,
                        2
                    )
                );

                $nextRun = $cron->getNextRunDate($nextRun);
                if (! is_null($orderSchedule->schedule->recurrences)) {
                    $currentRecurrence++;
                }
            }
        }

        $this->series = array_values($series);
    }

    public function showTitle(): bool
    {
        return true;
    }
}
