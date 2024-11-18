<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\OrderForm;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class OrderPositionsMove extends OrderPositions
{
    #[Modelable]
    public OrderForm $form;

    public array $orderPositions = [];

    public string $view = 'flux::livewire.order.order-positions-move';

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('order_id', $this->form->id);
    }
}
