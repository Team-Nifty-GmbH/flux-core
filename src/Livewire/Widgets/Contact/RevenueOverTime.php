<?php

namespace FluxErp\Livewire\Widgets\Contact;

use FluxErp\Livewire\Contact\Statistics;
use FluxErp\Livewire\Widgets\TotalRevenue;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class RevenueOverTime extends TotalRevenue
{
    public ?int $contactId = null;

    public static function dashboardComponent(): array|string
    {
        return Statistics::class;
    }

    protected function getRevenueQuery(Builder $query): Builder
    {
        return parent::getRevenueQuery($query)
            ->where('contact_id', $this->contactId);
    }

    protected function liveUpdates(): array
    {
        return [
            $this->contactChannel($this->contactId) => [
                Order::class => ['locked'],
            ],
        ];
    }
}
