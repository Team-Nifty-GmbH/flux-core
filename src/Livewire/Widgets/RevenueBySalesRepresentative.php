<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Charts\CircleChart;
use FluxErp\Models\Order;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;

class RevenueBySalesRepresentative extends CircleChart
{
    use Widgetable;

    public ?array $chart = [
        'type' => 'donut',
    ];

    public bool $showTotals = false;

    public function getPlotOptions(): array
    {
        return [
            'pie' => [
                'donut' => [
                    'labels' => [
                        'show' => true,
                        'total' => [
                            'show' => true,
                            'label' => __('Total'),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function calculateChart(): void
    {
        $timeFrame = $this->timeFrame;

        $baseQuery = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->whereNotNull('agent_id')
            ->when($timeFrame === TimeFrameEnum::Custom && $this->start, function (Builder $query) {
                $query->where('invoice_date', '>=', $this->start);
            })
            ->when($timeFrame === TimeFrameEnum::Custom && $this->end, function (Builder $query) {
                $query->where('invoice_date', '<=', $this->end);
            });

        if ($timeFrame !== TimeFrameEnum::Custom) {
            $parameters = $timeFrame->dateQueryParameters('invoice_date');

            if ($parameters && count($parameters) > 0) {
                if ($parameters['operator'] === 'between') {
                    $baseQuery->whereBetween($parameters['column'], $parameters['value']);
                } else {
                    $baseQuery->where(...array_values($parameters));
                }
            }
        }

        $revenueBySalesRepresentative = $baseQuery
            ->join('users', 'users.id', '=', 'agent_id')
            ->whereHas('orderType', function (Builder $query) {
                $query->whereNotIn('order_type_enum', ['purchase', 'purchase-refund']);
            })
            ->selectRaw('ROUND(SUM(total_net_price), 2) as total, agent_id, users.firstname, users.lastname')
            ->groupBy('agent_id', 'users.firstname', 'users.lastname')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->firstname . ' ' . $item->lastname => (float) $item->total];
            })
            ->toArray();

        $this->series = array_values($revenueBySalesRepresentative);
        $this->labels = array_keys($revenueBySalesRepresentative);
    }
}
