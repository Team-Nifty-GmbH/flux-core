<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\DataTables\StockPostingList as BaseStockPostingList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ExpiringStockList extends BaseStockPostingList
{
    protected const DAYS_RULES = 'required|integer|min:1|max:3650';

    protected const DEFAULT_DAYS = 30;

    #[Url]
    public int $days = self::DEFAULT_DAYS;

    public array $enabledCols = [
        'product.name',
        'lot.lot_number',
        'lot.expires_at',
        'warehouse.name',
        'storage_area.code',
        'remaining_stock',
    ];

    protected ?string $includeBefore = 'flux::livewire.product.expiring-stock-list';

    public function booted(): void
    {
        if (validator(['days' => $this->days], ['days' => static::DAYS_RULES])->fails()) {
            $this->days = static::DEFAULT_DAYS;
        }
    }

    public function updatedDays(): void
    {
        $this->loadData();
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->expiringWithin($this->days);
    }
}
