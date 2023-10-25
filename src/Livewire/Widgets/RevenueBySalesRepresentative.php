<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\UserWidget;
use FluxErp\Livewire\Charts\DonutChart;
use FluxErp\Models\Order;
use Illuminate\Support\Str;

class RevenueBySalesRepresentative extends DonutChart implements UserWidget
{
    public bool $showTotals = false;

    public static function getLabel(): string
    {
        return Str::headline(class_basename(self::class));
    }

    public function calculateChart(): void
    {
        $baseQuery = Order::query()
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->whereNotNull('agent_id')
            ->where('invoice_date', '>=', now()->subYear());

        $revenueBySalesRepresentative = $baseQuery->clone()
            ->join('users', 'users.id', '=', 'agent_id')
            ->whereHas('orderType', function ($query) {
                $query->whereNotIn('order_type_enum', ['purchase', 'purchase-refund']);
            })
            ->selectRaw('ROUND(SUM(total_net_price), 2) as total, agent_id, users.firstname, users.lastname')
            ->groupBy('agent_id', 'users.firstname', 'users.lastname')
            ->get()
            ->mapWithKeys(function ($item) {
                return ["{$item->firstname} {$item->lastname}" => (float) $item->total];
            })
            ->toArray();

        $this->series = array_values($revenueBySalesRepresentative);
        $this->labels = array_keys($revenueBySalesRepresentative);
    }
}
