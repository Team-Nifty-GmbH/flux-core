<?php

namespace FluxErp\Livewire\Widgets;

use Illuminate\Database\Eloquent\Builder;

class RecurringCostsForecast extends RecurringRevenueForecast
{
    protected function scopeOrders(Builder $query): Builder
    {
        return $query->purchase();
    }
}
