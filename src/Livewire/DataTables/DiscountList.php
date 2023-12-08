<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Discount;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;

class DiscountList extends DataTable
{
    protected string $model = Discount::class;

    public array $enabledCols = [
        'model.name',
        'discount',
    ];

    public array $formatters = [
        'discount' => 'percentage',
    ];

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with('model');
    }
}
