<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\DataTables\TransactionList;
use FluxErp\Livewire\Forms\OrderForm;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class Accounting extends TransactionList
{
    #[Modelable]
    public OrderForm $order;

    protected string $view = 'flux::livewire.order.accounting';

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('order_id', $this->order->id);
    }
}
