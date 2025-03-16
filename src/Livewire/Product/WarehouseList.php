<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\DataTables\WarehouseList as BaseWarehouseList;
use FluxErp\Livewire\Forms\ProductForm;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Attributes\Modelable;
use TeamNiftyGmbH\DataTable\Helpers\AggregatableRelationColumn;

class WarehouseList extends BaseWarehouseList
{
    public array $enabledCols = [
        'name',
        'stock_postings_sum_posting',
    ];

    public ?bool $isSearchable = false;

    #[Modelable]
    public ProductForm $product;

    public ?int $warehouseId = null;

    protected string $view = 'flux::livewire.product.warehouse-list';

    public function getColLabels(?array $cols = null): array
    {
        return array_merge(parent::getColLabels($cols), [
            'stock_postings_sum_posting' => __('Stock'),
        ]);
    }

    public function getFilterableColumns(?string $name = null): array
    {
        return $this->enabledCols;
    }

    public function getFormatters(): array
    {
        return array_merge(
            parent::getFormatters(),
            [
                'stock_postings_sum_posting' => 'coloredFloat',
            ]
        );
    }

    protected function getAggregatableRelationCols(): array
    {
        return [
            AggregatableRelationColumn::make(
                [
                    'stockPostings' => function ($query): void {
                        $query->where('product_id', $this->product->id);
                    },
                ],
                'posting'
            ),
        ];
    }

    protected function getRowAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-bind:class' => <<<'JS'
                    record.id === $wire.warehouseId && 'bg-indigo-100 dark:bg-indigo-800'
                JS,
        ]);
    }
}
