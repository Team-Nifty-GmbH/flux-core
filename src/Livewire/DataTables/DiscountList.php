<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Discount;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class DiscountList extends DataTable
{
    protected string $model = Discount::class;

    public array $enabledCols = [
        'model.name',
        'discount',
    ];

    public array $availableRelations = ['*'];

    public array $formatters = [
        'discount' => 'percentage',
    ];

    public function mount(): void
    {
        $this->availableCols = ModelInfo::forModel($this->model)
            ->attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with('model');
    }
}
