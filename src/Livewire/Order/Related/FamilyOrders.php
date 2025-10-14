<?php

namespace FluxErp\Livewire\Order\Related;

use FluxErp\Livewire\DataTables\OrderList;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;

class FamilyOrders extends OrderList
{
    #[Locked]
    public int $orderId;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->whereKey(
            resolve_static(Order::class, 'query')
                ->whereKey(
                    resolve_static(Order::class, 'query')
                        ->whereKey($this->orderId)
                        ->first([
                            'id',
                            'parent_id',
                        ])
                        ?->familyRootKey()
                )
                ->first([
                    'id',
                    'parent_id',
                ])
                ?->descendantKeys()
        );
    }
}
