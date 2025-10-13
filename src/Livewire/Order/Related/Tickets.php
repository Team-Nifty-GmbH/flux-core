<?php

namespace FluxErp\Livewire\Order\Related;

use FluxErp\Livewire\DataTables\TicketList;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class Tickets extends TicketList
{
    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('model_id', $this->modelId)->where('model_type', morph_alias(Order::class));
    }
}
