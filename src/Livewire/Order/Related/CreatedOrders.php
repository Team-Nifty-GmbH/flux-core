<?php

namespace FluxErp\Livewire\Order\Related;

use FluxErp\Livewire\DataTables\OrderList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;

class CreatedOrders extends OrderList
{
    #[Locked]
    public int $orderId;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('created_from_id', $this->orderId);
    }
}
