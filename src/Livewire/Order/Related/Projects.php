<?php

namespace FluxErp\Livewire\Order\Related;

use FluxErp\Livewire\DataTables\ProjectList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;

class Projects extends ProjectList
{
    #[Locked]
    public int $orderId;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where(fn (Builder $query) => $query
            ->where('order_id', $this->orderId)
            ->orWhereRelation('supplementaryOrders', 'orders.id', $this->orderId)
        );
    }
}
