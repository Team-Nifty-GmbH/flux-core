<?php

namespace FluxErp\Livewire\Order\Related;

use FluxErp\Livewire\DataTables\OrderList;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;

class SiblingOrders extends OrderList
{
    #[Locked]
    public int $orderId;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->whereKey(
            resolve_static(Order::class, 'query')
                ->whereKey(
                    resolve_static(Order::class, 'query')
                        ->whereKey(
                            resolve_static(Order::class, 'query')
                                ->whereKey($this->orderId)
                                ->first([
                                    'id',
                                    'parent_id',
                                ])
                                ->ancestorKeys()
                        )
                        ->whereNull('parent_id')
                        ->first([
                            'id',
                            'parent_id',
                        ])
                        ->getKey()
                )
                ->first([
                    'id',
                    'parent_id',
                ])
                ->descendantKeys()
        );
    }
}
