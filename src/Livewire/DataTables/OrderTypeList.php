<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\OrderType;
use Illuminate\Database\Eloquent\Builder;

class OrderTypeList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'description',
        'tenant.name',
        'order_type_enum',
        'print_layouts',
    ];

    protected string $model = OrderType::class;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with('tenant:id,name');
    }
}
