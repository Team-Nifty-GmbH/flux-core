<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\OrderType;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class OrderTypeList extends BaseDataTable
{
    protected string $model = OrderType::class;

    public array $enabledCols = [
        'name',
        'description',
        'client.name',
        'order_type_enum',
        'print_layouts',
    ];

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('Create'))
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$dispatch(\'data-table-row-clicked\')',
                ]),
        ];
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with('client:id,name');
    }
}
