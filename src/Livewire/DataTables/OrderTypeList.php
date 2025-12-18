<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\OrderType;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

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

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('indigo')
                ->text(__('Create'))
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$dispatch(\'data-table-row-clicked\')',
                ]),
        ];
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with('tenant:id,name');
    }
}
