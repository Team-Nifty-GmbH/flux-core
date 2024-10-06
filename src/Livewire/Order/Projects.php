<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\DataTables\ProjectList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class Projects extends ProjectList
{
    #[Modelable]
    public int $orderId;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('order_id', $this->orderId);
    }
}
