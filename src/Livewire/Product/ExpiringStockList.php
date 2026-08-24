<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\DataTables\StockPostingList as BaseStockPostingList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ExpiringStockList extends BaseStockPostingList
{
    #[Url]
    public int $days = 30;

    public array $enabledCols = [
        'product.name',
        'lot.lot_number',
        'lot.expires_at',
        'warehouse.name',
        'warehouse_bin.code',
        'remaining_stock',
    ];

    protected ?string $includeBefore = 'flux::livewire.product.expiring-stock-list';

    public function updatedDays(): void
    {
        $this->loadData();
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->expiringWithin($this->days);
    }
}
