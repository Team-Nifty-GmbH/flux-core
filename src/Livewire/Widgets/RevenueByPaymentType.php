<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\Order as OrderModel;
use FluxErp\Models\PaymentType;
use FluxErp\Support\Metrics\Charts\Bar;
use FluxErp\Traits\Livewire\Widget\HasTemporalXAxisFormatter;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use FluxErp\Traits\Livewire\Widget\MoneyChartFormattingTrait;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class RevenueByPaymentType extends BarChart implements HasWidgetOptions
{
    use HasTemporalXAxisFormatter, IsTimeFrameAwareWidget, MoneyChartFormattingTrait;

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

    public array $optionData = [];

    public bool $showTotals = false;

    public static function getCategory(): ?string
    {
        return 'Revenue';
    }

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public function calculateChart(): void
    {
        $paymentMethods = resolve_static(PaymentType::class, 'query')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->series = [];
        $this->optionData = [];
        $categories = null;

        foreach ($paymentMethods as $paymentMethod) {
            $query = resolve_static(OrderModel::class, 'query')
                ->where('payment_type_id', $paymentMethod->getKey())
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->revenue();

            $result = Bar::make($query)
                ->setDateColumn('invoice_date')
                ->setRange($this->timeFrame)
                ->setEndingDate($this->getEnd())
                ->setStartingDate($this->getStart())
                ->sum('total_net_price');

            $formattedPaymentMethodName = $paymentMethod->name;

            $this->series[] = [
                'name' => $formattedPaymentMethodName,
                'data' => $result->getData(),
            ];

            $this->optionData[] = [
                'label' => $formattedPaymentMethodName,
                'payment_type_id' => $paymentMethod->getKey(),
            ];

            $categories ??= $result->getLabels();
        }

        $this->xaxis = [
            'categories' => $categories ?? [],
        ];
    }

    #[Renderless]
    public function options(): array
    {
        return array_map(
            fn (array $data) => [
                'label' => data_get($data, 'label'),
                'method' => 'show',
                'params' => [
                    'payment_type_id' => data_get($data, 'payment_type_id'),
                    'name' => data_get($data, 'label'),
                ],
            ],
            $this->optionData
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
                ->where('payment_type_id', $paymentTypeId)
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->revenue()
                ->whereBetween('invoice_date', [$start, $end]),
            __('Payment Type: :name', ['name' => $name]) . ' ' .
            __('between :start and :end', ['start' => $start, 'end' => $end]),
        )
            ->store();

        $this->redirectRoute('orders.orders', navigate: true);
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateChart();
        $this->updateData();
    }
}
