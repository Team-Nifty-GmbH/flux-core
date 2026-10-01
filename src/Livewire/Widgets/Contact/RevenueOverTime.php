<?php

namespace FluxErp\Livewire\Widgets\Contact;

use FluxErp\Livewire\Contact\Statistics;
use FluxErp\Livewire\Widgets\TotalRevenue;
use Illuminate\Database\Eloquent\Builder;

class RevenueOverTime extends TotalRevenue
{
    public ?int $contactId = null;

    public static function dashboardComponent(): array|string
    {
        return Statistics::class;
    }

    protected function getRevenueQuery(Builder $builder): Builder
    {
        return parent::getRevenueQuery($builder)
            ->where('contact_id', $this->contactId);
    }
}
