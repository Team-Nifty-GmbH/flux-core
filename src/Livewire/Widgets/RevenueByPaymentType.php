<?php

namespace FluxErp\Livewire\Widgets;

use Carbon\CarbonPeriod;
use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentType;
use FluxErp\Traits\Livewire\Widget\HasTemporalXAxisFormatter;
use FluxErp\Traits\Livewire\Widget\HasTrendExpressions;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use FluxErp\Traits\Livewire\Widget\MoneyChartFormattingTrait;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class RevenueByPaymentType extends BarChart implements HasWidgetOptions
{
    use HasTemporalXAxisFormatter, HasTrendExpressions, IsTimeFrameAwareWidget, MoneyChartFormattingTrait;

    public ?array $chart = [
        'type' => 'bar',
        'stacked' => true,
    ];

    public ?array $plotOptions = [
        'bar' => [
            'horizontal' => false,
            'columnWidth' => '75%',
        ],
    ];

    public bool $showTotals = false;

    public static function getCategory(): ?string
    {
        return 'Revenue';
    }

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
        $paymentTypes = resolve_static(PaymentType::class, 'query')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->series = [];

        $start = $this->getStart();
        $end = $this->getEnd();
        $unit = $this->getUnit() ?? 'day';
        $format = $this->getTrendFormat($unit);

        $orderQuery = resolve_static(Order::class, 'query');
        $driver = $orderQuery->getConnection()->getDriverName();
        $grammar = $orderQuery->getQuery()->getGrammar();
        $wrappedDateColumn = $grammar->wrap('invoice_date');

        $dateExpression = $this->getTrendExpression($driver, $wrappedDateColumn, $unit);

        $rows = $orderQuery
            ->revenue()
            ->whereNotNull('invoice_date')
            ->whereBetween('invoice_date', [$start, $end])
            ->whereNotNull('invoice_number')
            ->selectRaw("{$dateExpression} as period, payment_type_id, sum({$grammar->wrap('total_net_price')}) as total")
            ->groupBy('period', 'payment_type_id')
            ->get(['period', 'payment_type_id', 'total']);

        $periods = collect(CarbonPeriod::create($start, '1 ' . $unit, $end))
            ->map(fn ($d) => $d->format($format))
            ->all();

        $groupedByPayment = $rows->groupBy('payment_type_id');

        foreach ($paymentTypes as $paymentType) {
            $byPeriod = $groupedByPayment->get($paymentType->getKey())?->keyBy('period') ?? collect();

            $this->series[] = [
                'name' => $paymentType->name,
                'data' => array_map(fn ($p) => (float) ($byPeriod->get($p)?->total ?? 0), $periods),
                'payment_type_id' => $paymentType->getKey(),
            ];
        }

        $this->xaxis = [
            'categories' => $periods,
        ];
    }

    #[Renderless]
    public function options(): array
    {
        return array_map(
            fn (array $seriesItem) => [
                'label' => data_get($seriesItem, 'name'),
                'method' => 'show',
                'params' => [
                    'payment_type_id' => data_get($seriesItem, 'payment_type_id'),
                    'name' => data_get($seriesItem, 'name'),
                ],
            ],
            $this->series
        );
    }

    #[Renderless]
    public function show(array $params): void
    {
        $paymentTypeId = data_get($params, 'payment_type_id');
        $name = data_get($params, 'name');
        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        SessionFilter::make(
            Livewire::new(resolve_static(OrderList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->revenue()
                ->where('payment_type_id', $paymentTypeId)
                ->whereNotNull('invoice_date')
                ->whereBetween('invoice_date', [$start, $end])
                ->whereNotNull('invoice_number'),
            __('Payment Type: :name', ['name' => $name]) . ' '
            . __('between :start and :end', ['start' => $start, 'end' => $end]),
        )
            ->store();

        $this->redirectRoute('orders.orders', navigate: true);
    }
}
