<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\DataTables\StockPostingList as BaseStockPostingList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Attributes\Modelable;

class StockPostingList extends BaseStockPostingList
{
    #[Modelable]
    public ?int $warehouseId = null;

    public int $productId;

    public function getComponentAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-init' => <<<'JS'
                $watch('$wire.warehouseId', () => {
                    $wire.loadData();
                })
            JS,
        ]);
    }

    public function updatedWarehouseId(): void
    {
        $this->userFilters = [[
            [
                'column' => 'warehouse_id',
                'operator' => '=',
                'value' => $this->warehouseId,
            ],
        ]];
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder
            ->where('product_id', $this->productId);
    }
}
