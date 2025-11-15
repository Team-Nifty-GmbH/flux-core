<?php

namespace FluxErp\Livewire\Widgets;

use Cron\CronExpression;
use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\Pivots\OrderSchedule;
use FluxErp\Traits\Livewire\HasTemporalXAxisFormatter;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;
use Throwable;

class RecurringRevenueForecast extends BarChart implements HasWidgetOptions
{
    use HasTemporalXAxisFormatter, IsTimeFrameAwareWidget, MoneyChartFormattingTrait, Widgetable;

    public ?array $chart = [
        'type' => 'bar',
    ];

    #[Locked]
    public ?array $orderIds = [];

    public ?array $plotOptions = [
        'bar' => [
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

    #[Renderless]
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
                'order:id,tenant_id,total_net_price',
                'order.tenant:id,name',
                'schedule:id,cron_expression,ends_at,recurrences,current_recurrence',
            ])
            ->get();

        $series = [];
        $dates = [];
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

            $index = $orderSchedule->order->tenant_id;
            $currentRecurrence = $orderSchedule->schedule->current_recurrence;
            while (
                $nextRun <= $this->getEnd()
                && $nextRun >= $this->getStart()
                && (
                    is_null($orderSchedule->schedule->ends_at)
                    || $nextRun <= $orderSchedule->schedule->ends_at
                )
                && (
                    is_null($orderSchedule->schedule->recurrences)
                    || $currentRecurrence < $orderSchedule->schedule->recurrences
                )
            ) {
                $tenant = $orderSchedule->order->tenant;
                if (! data_get($series, $index . '.name')) {
                    data_set($series, $index . '.name', $tenant->name);
                }

                $dates[] = $nextRun->format('Y-m-d');
                $this->orderIds = array_merge($this->orderIds, [$orderSchedule->order->getKey()]);

                data_set(
                    $series,
                    $index . '.data.' . $nextRun->format('Y-m-d'),
                    bcadd(
                        data_get($series, $index . '.data.' . $nextRun->format('Y-m-d')) ?? 0,
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

        $allDates = collect($dates)->unique()->sort()->values();

        $this->xaxis['categories'] = $allDates->toArray();

        $this->series = collect($series)
            ->map(function (array $series) use ($allDates) {
                $seriesData = data_get($series, 'data', []);

                $orderedData = $allDates->map(function ($date) use ($seriesData) {
                    return data_get($seriesData, $date) ?? 0;
                });

                $series['data'] = $orderedData->toArray();

                return $series;
            })
            ->values()
            ->toArray();
    }

    public function options(): array
    {
        return [
            [
                'label' => __('Show'),
                'method' => 'show',
            ],
        ];
    }

    #[Renderless]
    public function show(): void
    {
        $orderIds = $this->orderIds;

        SessionFilter::make(
            Livewire::new(resolve_static(OrderList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query->whereKey($orderIds),
            __(static::getLabel()),
        )
            ->store();

        $this->redirectRoute('orders.orders', navigate: true);
    }

    public function showTitle(): bool
    {
        return true;
    }
}
