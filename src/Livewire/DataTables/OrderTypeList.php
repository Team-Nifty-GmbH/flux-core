<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\OrderType;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class OrderTypeList extends DataTable
{
    protected string $model = OrderType::class;

    public array $enabledCols = [
        'name',
        'description',
        'client.name',
        'order_type_enum',
        'print_layouts',
    ];

    public array $columnLabels = [
        'name' => 'Name',
        'description' => 'Description',
        'client.name' => 'Client',
        'order_type_enum' => 'Order Type',
        'print_layouts' => 'Print Layouts',
    ];

    public array $sortable = ['*'];

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

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with('client:id,name');
    }

    public function itemToArray($item): array
    {
        $item = parent::itemToArray($item);
        $item['print_layouts'] = implode(', ', $item['print_layouts'] ?? []);

        return $item;
    }
}
