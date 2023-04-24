<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\OrderType;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class OrderTypeList extends DataTable
{
    protected string $model = OrderType::class;

    public array $enabledCols = [
        'name',
        'description',
        'client.name',
        'order_type_enum',
        'print_layouts'
    ];

    public array $sortable = ['*'];

    public function mount(): void
    {
        $attributes = ModelInfo::forModel(OrderType::class)->attributes;

        $this->availableCols = $attributes->pluck('name')->toArray();

        parent::mount();
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('New order type'))
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
}
