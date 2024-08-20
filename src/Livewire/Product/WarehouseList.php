<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\DataTables\WarehouseList as BaseWarehouseList;
use FluxErp\Livewire\Forms\ProductForm;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Attributes\Modelable;
use TeamNiftyGmbH\DataTable\Helpers\AggregatableRelationColumn;

class WarehouseList extends BaseWarehouseList
{
    protected string $view = 'flux::livewire.product.warehouse-list';

    #[Modelable]
    public ProductForm $product;

    public array $enabledCols = [
        'name',
        'stock_postings_sum_posting',
    ];

    public ?bool $isSearchable = false;

    public ?int $warehouseId = null;

    public function getFormatters(): array
    {
        return array_merge(
            parent::getFormatters(),
            [
                'stock_postings_sum_posting' => 'coloredFloat',
            ]
        );
    }

    public function getFilterableColumns(?string $name = null): array
    {
        return $this->enabledCols;
    }

    protected function getAggregatableRelationCols(): array
    {
        return [
            AggregatableRelationColumn::make(
                [
                    'stockPostings' => function ($query) {
                        $query->where('product_id', $this->product->id);
                    },
                ],
                'posting'
            ),
        ];
    }

    public function getColLabels(?array $cols = null): array
    {
        return array_merge(parent::getColLabels($cols), [
            'stock_postings_sum_posting' => __('Stock'),
        ]);
    }

    protected function getRowAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-bind:class' => <<<'JS'
                    record.id === $wire.warehouseId && 'bg-primary-100 dark:bg-primary-800'
                JS,
        ]);
    }
}
