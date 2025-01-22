<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Discount;
use Illuminate\Database\Eloquent\Builder;

class DiscountList extends BaseDataTable
{
    protected string $model = Discount::class;

    public array $enabledCols = [
        'model.name',
        'discount',
        'is_percentage',
    ];

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with('model');
    }
}
