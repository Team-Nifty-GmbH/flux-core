<?php

namespace FluxErp\Livewire\Widgets\Project;

use FluxErp\Livewire\Widgets\ProjectMargin;
use Illuminate\Database\Eloquent\Builder;

class Revenue extends ProjectMargin
{
    protected function getOrderQuery(?Builder $query = null): Builder
    {
        return parent::getOrderQuery($query)
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->revenue();
    }

    protected function sumColumn(): string
    {
        return 'total_net_price';
    }
}
